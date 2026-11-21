<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * SCRUM-35: dasbor keaktifan dosen untuk Kaprodi.
 *
 * Definisi (sama dengan teks "Cara membaca aktivitas" di Figma):
 * - Konsultasi ditanggapi  : konsultasi berstatus ditanggapi.
 * - Bimbingan diproses     : bimbingan yang diputuskan dosen (disetujui, selesai, atau ditolak dosen).
 *   Bimbingan yang ditolak otomatis karena dosen tidak merespons TIDAK dihitung sebagai keputusan dosen;
 *   angkanya ditampilkan terpisah sebagai "tidak direspons".
 * - Waktu tanggapan        : selisih waktu pengajuan hingga tanggapan/keputusan pertama dosen.
 * - Tanggal aktivitas      : tanggal respons bila sudah direspons, selain itu tanggal pengajuan.
 *   Sebuah permintaan masuk ke periode sesuai tanggal aktivitasnya.
 */
class AktivitasDosenController extends Controller
{
    private const ZONA = 'Asia/Jakarta';

    /** Rentang tanggal tiap minggu pada grafik mingguan. */
    private const MINGGU = [[1, 7], [8, 14], [15, 21], [22, 28], [29, 31]];

    /** Banyak bulan pada pilihan periode (termasuk bulan ini). */
    private const BANYAK_PERIODE = 6;

    // Dasbor: ringkasan + empat grafik
    public function dashboard(Request $request): View
    {
        $this->siapkan($request);

        $periode = $this->periode($request);
        $daftar = $this->daftarDosen($request);
        $aktivitas = $this->aktivitas($daftar['dosen']->modelKeys(), $periode['mulai'], $periode['akhir']);
        $ringkasan = $this->ringkasan($daftar['dosen'], $aktivitas);

        return view('kaprodi.dashboard', $periode + $daftar + [
            'stat' => $this->statistik($daftar['dosen'], $ringkasan, $aktivitas),
            'grafik' => $this->grafik($ringkasan, $this->mingguan($aktivitas, $periode['mulaiZona'])),
        ]);
    }

    // Aktivitas dosen: kartu ringkasan + tabel per dosen
    public function index(Request $request): View
    {
        $this->siapkan($request);

        $periode = $this->periode($request);
        $daftar = $this->daftarDosen($request);
        $aktivitas = $this->aktivitas($daftar['dosen']->modelKeys(), $periode['mulai'], $periode['akhir']);
        $ringkasan = $this->ringkasan($daftar['dosen'], $aktivitas);

        return view('kaprodi.aktivitas', $periode + $daftar + [
            'ringkasan' => $ringkasan,
            'stat' => $this->statistik($daftar['dosen'], $ringkasan, $aktivitas),
        ]);
    }

    // Detail aktivitas satu dosen
    public function show(Request $request, User $dosen): View
    {
        $this->siapkan($request);
        abort_unless($dosen->role === 'dosen', 404);

        $periode = $this->periode($request);
        $dosen->loadCount('keahlian')->load('keahlian');

        $aktivitas = $this->aktivitas([$dosen->getKey()], $periode['mulai'], $periode['akhir']);
        $metrik = $this->metrik($aktivitas, $dosen);
        $diperbarui = DB::table('keahlian_dosen')->where('user_id', $dosen->getKey())->max('updated_at');

        return view('kaprodi.aktivitas-show', $periode + [
            'dosen' => $dosen,
            'nama' => $this->namaRingkas($dosen->name),
            'm' => $metrik,
            'durasi' => $this->formatDurasi($metrik['rata_jam']),
            'tags' => $dosen->keahlian->pluck('nama_bidang'),
            'diperbarui' => $diperbarui ? Carbon::parse($diperbarui) : null,
            'mingguan' => $this->mingguan($aktivitas, $periode['mulaiZona']),
            'rincian' => $this->rincian($aktivitas),
            'dataHingga' => min(Carbon::now(), $periode['akhir']),
        ]);
    }

    /**
     * Hanya Kaprodi. Pengajuan bimbingan yang sudah lewat 1 hari ditolak dulu supaya
     * angka "tidak direspons" akurat (sama seperti halaman bimbingan lain).
     */
    private function siapkan(Request $request): void
    {
        abort_unless($request->user()?->role === 'kaprodi', 403);

        Bimbingan::tolakKadaluarsa();
    }

    /**
     * Periode = bulan kalender (WIB). Enam bulan terakhir, bulan ini default.
     *
     * @return array<string, mixed>
     */
    private function periode(Request $request): array
    {
        $bulanIni = Carbon::now(self::ZONA)->startOfMonth();

        $opsi = collect(range(0, self::BANYAK_PERIODE - 1))
            ->map(fn (int $i): array => [
                'key' => $bulanIni->copy()->subMonths($i)->format('Y-m'),
                'label' => $bulanIni->copy()->subMonths($i)->locale('id')->translatedFormat('F Y'),
            ])
            ->all();

        $diminta = (string) $request->query('periode');
        $key = collect($opsi)->contains('key', $diminta) ? $diminta : $opsi[0]['key'];

        $mulaiZona = Carbon::createFromFormat('Y-m-d', $key.'-01', self::ZONA)->startOfDay();
        $zonaApp = config('app.timezone');

        return [
            'periodeKey' => $key,
            'periodeLabel' => collect($opsi)->firstWhere('key', $key)['label'],
            'opsiPeriode' => $opsi,
            'mulaiZona' => $mulaiZona,
            'mulai' => $mulaiZona->copy()->setTimezone($zonaApp),
            'akhir' => $mulaiZona->copy()->endOfMonth()->setTimezone($zonaApp),
        ];
    }

    /**
     * Dosen yang dievaluasi (role dosen; Kaprodi sendiri tidak ikut) dan filter pilihan dosen.
     *
     * @return array<string, mixed>
     */
    private function daftarDosen(Request $request): array
    {
        $semua = User::query()->where('role', 'dosen')->withCount('keahlian')->orderBy('name')->get();
        $id = (int) $request->query('dosen');
        $dipilih = $id > 0 ? $semua->firstWhere('id', $id) : null;

        return [
            'semuaDosen' => $semua,
            'dosenId' => $dipilih?->getKey(),
            'dosen' => $dipilih ? $semua->where('id', $dipilih->getKey())->values() : $semua,
        ];
    }

    /**
     * Semua konsultasi dan bimbingan dosen yang "jatuh" di periode (lihat definisi di atas),
     * dalam bentuk baris seragam.
     *
     * @param  list<int>  $ids
     * @return Collection<int, array<string, mixed>>
     */
    private function aktivitas(array $ids, Carbon $mulai, Carbon $akhir): Collection
    {
        if ($ids === []) {
            return collect();
        }

        $konsultasi = $this->dalamPeriode(Konsultasi::query()->whereIn('dosen_id', $ids), $mulai, $akhir)
            ->with(['mahasiswa:id,name'])
            ->get()
            ->map(function (Konsultasi $k): array {
                $diproses = in_array($k->status, [Konsultasi::DITANGGAPI, Konsultasi::DITOLAK], true);

                return [
                    'jenis' => 'Konsultasi',
                    'judul' => $k->kode.' · '.($k->mahasiswa?->name ?? '-'),
                    'dosen_id' => $k->dosen_id,
                    'status' => $k->status,
                    'otomatis' => false,
                    'diproses' => $diproses,
                    'dibuat' => $k->created_at,
                    'direspons' => $k->responded_at,
                    'durasi_jam' => $diproses ? $this->selisihJam($k->created_at, $k->responded_at) : null,
                ];
            });

        $bimbingan = $this->dalamPeriode(Bimbingan::query()->whereIn('dosen_id', $ids), $mulai, $akhir)
            ->get()
            ->map(function (Bimbingan $b): array {
                $otomatis = (bool) $b->ditolak_otomatis;
                $diproses = ! $otomatis
                    && in_array($b->status, [Bimbingan::DISETUJUI, Bimbingan::SELESAI, Bimbingan::DITOLAK], true);

                return [
                    'jenis' => 'Bimbingan',
                    'judul' => $b->kode.' · '.$b->nama_tim,
                    'dosen_id' => $b->dosen_id,
                    'status' => $b->status,
                    'otomatis' => $otomatis,
                    'diproses' => $diproses,
                    'dibuat' => $b->created_at,
                    'direspons' => $b->responded_at,
                    'durasi_jam' => $diproses ? $this->selisihJam($b->created_at, $b->responded_at) : null,
                ];
            });

        return collect()->merge($konsultasi)->merge($bimbingan)->values();
    }

    /** Masuk periode: direspons dalam periode, atau belum direspons dan diajukan dalam periode. */
    private function dalamPeriode(Builder $query, Carbon $mulai, Carbon $akhir): Builder
    {
        return $query->where(function (Builder $q) use ($mulai, $akhir): void {
            $q->whereBetween('responded_at', [$mulai, $akhir])
                ->orWhere(fn ($belum) => $belum->whereNull('responded_at')->whereBetween('created_at', [$mulai, $akhir]));
        });
    }

    private function selisihJam(?Carbon $dibuat, ?Carbon $direspons): ?float
    {
        if ($dibuat === null || $direspons === null) {
            return null;
        }

        return $dibuat->diffInSeconds($direspons, true) / 3600;
    }

    /**
     * Angka per dosen.
     *
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function metrik(Collection $items, User $dosen): array
    {
        $konsultasi = $items->where('jenis', 'Konsultasi');
        $bimbingan = $items->where('jenis', 'Bimbingan');
        $durasi = $items->where('diproses', true)->pluck('durasi_jam')->filter(fn ($jam): bool => $jam !== null);

        $setuju = $bimbingan->whereIn('status', [Bimbingan::DISETUJUI, Bimbingan::SELESAI])->count();
        $tolak = $bimbingan->where('status', Bimbingan::DITOLAK)->where('otomatis', false)->count();

        return [
            'konsultasi_ditanggapi' => $konsultasi->where('status', Konsultasi::DITANGGAPI)->count(),
            'konsultasi_menunggu' => $konsultasi->where('status', Konsultasi::MENUNGGU)->count(),
            'bimbingan_setuju' => $setuju,
            'bimbingan_tolak' => $tolak,
            'bimbingan_diproses' => $setuju + $tolak,
            'bimbingan_otomatis' => $bimbingan->where('status', Bimbingan::DITOLAK)->where('otomatis', true)->count(),
            'bimbingan_menunggu' => $bimbingan->where('status', Bimbingan::MENUNGGU)->count(),
            'rata_jam' => $durasi->isEmpty() ? null : (float) $durasi->avg(),
            'tag' => (int) $dosen->keahlian_count,
        ];
    }

    /**
     * Satu baris per dosen untuk tabel dan grafik.
     *
     * @param  Collection<int, User>  $dosen
     * @param  Collection<int, array<string, mixed>>  $aktivitas
     * @return Collection<int, array<string, mixed>>
     */
    private function ringkasan(Collection $dosen, Collection $aktivitas): Collection
    {
        return $dosen->map(function (User $d) use ($aktivitas): array {
            $m = $this->metrik($aktivitas->where('dosen_id', $d->getKey()), $d);

            return [
                'dosen' => $d,
                'nama' => $this->namaRingkas($d->name),
                'm' => $m,
                'durasi' => $this->formatDurasi($m['rata_jam']),
                'keahlian' => $m['tag'] > 0 ? $m['tag'].' tag lengkap' : 'belum diisi',
            ];
        })->values();
    }

    /**
     * Kartu ringkasan di bagian atas halaman.
     *
     * @param  Collection<int, User>  $dosen
     * @param  Collection<int, array<string, mixed>>  $ringkasan
     * @param  Collection<int, array<string, mixed>>  $aktivitas
     * @return array<string, mixed>
     */
    private function statistik(Collection $dosen, Collection $ringkasan, Collection $aktivitas): array
    {
        $durasi = $aktivitas->where('diproses', true)->pluck('durasi_jam')->filter(fn ($jam): bool => $jam !== null);
        $setuju = (int) $ringkasan->sum('m.bimbingan_setuju');
        $tolak = (int) $ringkasan->sum('m.bimbingan_tolak');
        $belumLengkap = $dosen->filter(fn (User $d): bool => $d->keahlian_count < 1);

        return [
            'jumlah_dosen' => $dosen->count(),
            'konsultasi_ditanggapi' => (int) $ringkasan->sum('m.konsultasi_ditanggapi'),
            'bimbingan_setuju' => $setuju,
            'bimbingan_tolak' => $tolak,
            'bimbingan_diproses' => $setuju + $tolak,
            'bimbingan_otomatis' => (int) $ringkasan->sum('m.bimbingan_otomatis'),
            'rata' => $this->formatDurasi($durasi->isEmpty() ? null : (float) $durasi->avg()),
            'lengkap' => $dosen->count() - $belumLengkap->count(),
            'belum_lengkap' => $belumLengkap->map(fn (User $d): string => $this->namaRingkas($d->name))->values()->all(),
        ];
    }

    /**
     * Data empat grafik pada dasbor Kaprodi. Menggabungkan tabel users, konsultasi, bimbingan,
     * dan keahlian_dosen (jumlah tag per dosen).
     *
     * @param  Collection<int, array<string, mixed>>  $ringkasan
     * @param  list<array{label: string, jumlah: int}>  $mingguan
     * @return array<string, mixed>
     */
    private function grafik(Collection $ringkasan, array $mingguan): array
    {
        $labels = $ringkasan->pluck('nama')->all();

        return [
            'perDosen' => [
                'labels' => $labels,
                'series' => [
                    ['name' => 'Konsultasi ditanggapi', 'color' => '#2563eb', 'values' => $ringkasan->pluck('m.konsultasi_ditanggapi')->all()],
                    ['name' => 'Bimbingan diproses', 'color' => '#16a34a', 'values' => $ringkasan->pluck('m.bimbingan_diproses')->all()],
                ],
            ],
            'waktu' => [
                'labels' => $labels,
                'values' => $ringkasan->map(fn (array $r): ?float => $r['m']['rata_jam'] === null ? null : round($r['m']['rata_jam'] / 24, 2))->all(),
                'teks' => $ringkasan->pluck('durasi')->all(),
            ],
            'keputusan' => [
                'labels' => $labels,
                'series' => [
                    ['name' => 'Disetujui', 'color' => '#16a34a', 'values' => $ringkasan->pluck('m.bimbingan_setuju')->all()],
                    ['name' => 'Ditolak dosen', 'color' => '#dc2626', 'values' => $ringkasan->pluck('m.bimbingan_tolak')->all()],
                    ['name' => 'Tidak direspons', 'color' => '#94a3b8', 'values' => $ringkasan->pluck('m.bimbingan_otomatis')->all()],
                    ['name' => 'Menunggu', 'color' => '#d97706', 'values' => $ringkasan->pluck('m.bimbingan_menunggu')->all()],
                ],
            ],
            'mingguan' => [
                'labels' => array_column($mingguan, 'label'),
                'series' => [
                    ['name' => 'Aktivitas diproses', 'color' => '#2563eb', 'values' => array_column($mingguan, 'jumlah')],
                ],
            ],
        ];
    }

    /**
     * Aktivitas yang diproses dosen per minggu pada bulan terpilih.
     *
     * @param  Collection<int, array<string, mixed>>  $items
     * @return list<array{label: string, jumlah: int}>
     */
    private function mingguan(Collection $items, Carbon $mulaiZona): array
    {
        $hariTerakhir = $mulaiZona->copy()->endOfMonth()->day;
        $bulan = $mulaiZona->copy()->locale('id')->translatedFormat('M');
        $diproses = $items->where('diproses', true)->filter(fn (array $i): bool => $i['direspons'] !== null);

        $hasil = [];

        foreach (self::MINGGU as [$dari, $sampai]) {
            if ($dari > $hariTerakhir) {
                continue;
            }

            $sampai = min($sampai, $hariTerakhir);

            $hasil[] = [
                'label' => sprintf('%02d–%02d %s', $dari, $sampai, $bulan),
                'jumlah' => $diproses->filter(function (array $i) use ($dari, $sampai): bool {
                    $hari = $i['direspons']->copy()->setTimezone(self::ZONA)->day;

                    return $hari >= $dari && $hari <= $sampai;
                })->count(),
            ];
        }

        return $hasil;
    }

    /**
     * Tabel "Rincian aktivitas" pada halaman detail, terbaru di atas.
     *
     * @param  Collection<int, array<string, mixed>>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function rincian(Collection $items): Collection
    {
        return $items
            ->sortByDesc(fn (array $i): int => ($i['direspons'] ?? $i['dibuat'])->getTimestamp())
            ->map(function (array $i): array {
                $label = match (true) {
                    $i['otomatis'] => 'Ditolak otomatis',
                    default => ucfirst($i['status']),
                };

                return [
                    'judul' => $i['judul'],
                    'jenis' => $i['jenis'],
                    'status' => $label,
                    'badge' => $i['status'],
                    'waktu' => match (true) {
                        $i['diproses'] => $this->formatDurasi($i['durasi_jam']),
                        $i['otomatis'] => 'Tidak direspons',
                        default => 'Belum ditanggapi',
                    },
                ];
            })
            ->values();
    }

    /** Nama tanpa gelar: "Dimas Arunika, M.Kom." menjadi "Dimas Arunika". */
    private function namaRingkas(string $nama): string
    {
        return trim(Str::before($nama, ','));
    }

    /** "1,4 hari", "2 hari", "5 jam", atau "-" bila belum ada data. */
    private function formatDurasi(?float $jam): string
    {
        if ($jam === null) {
            return '-';
        }

        if ($jam < 1) {
            return '< 1 jam';
        }

        if ($jam < 24) {
            return round($jam).' jam';
        }

        $hari = rtrim(rtrim(number_format($jam / 24, 1, ',', '.'), '0'), ',');

        return $hari.' hari';
    }
}
