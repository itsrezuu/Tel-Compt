<?php

namespace App\Http\Controllers\AdminHima;

use App\Http\Controllers\Controller;
use App\Models\Lomba;
use App\Models\PengajuanPrestasi;
use App\Models\UsulanLomba;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * SCRUM-33: dasbor operasional Admin HIMA.
 * Angka "menunggu" memakai definisi yang sama dengan halaman Tinjau Usulan
 * dan Verifikasi & Arsip, supaya angka di dasbor cocok dengan tab di halaman itu.
 */
class DashboardController extends Controller
{
    private const ZONA = 'Asia/Jakarta';

    /** Jumlah baris pada tabel "Antrean prioritas". */
    private const BATAS_ANTREAN = 8;

    /** Lomba dianggap mendekati tenggat bila batas pendaftarannya dalam sekian hari. */
    private const HARI_MENDEKATI_TENGGAT = 7;

    public function index(Request $request): View
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $awalHariIni = Carbon::now(self::ZONA)->startOfDay()->setTimezone(config('app.timezone'));
        $batasTenggat = Carbon::now(self::ZONA)->addDays(self::HARI_MENDEKATI_TENGGAT)->toDateString();

        return view('admin-hima.dashboard', [
            'usulanMenunggu' => UsulanLomba::where('status', 'menunggu')->count(),
            'usulanBaru' => UsulanLomba::where('status', 'menunggu')->where('created_at', '>=', $awalHariIni)->count(),
            'prestasiMenunggu' => $this->prestasiMenunggu()->count(),
            'arsipMenunggu' => $this->arsipMenunggu()->count(),
            'lombaAktif' => Lomba::tayang()->masihBuka()->count(),
            'lombaMendekatiTenggat' => Lomba::tayang()->masihBuka()
                ->whereDate('tenggat_pendaftaran', '<=', $batasTenggat)->count(),
            'antrean' => $this->antrean(),
            'grafik' => [
                'tren' => $this->trenPengajuan(),
                'usulan' => $this->statusUsulan(),
                'kategori' => $this->lombaPerKategori(),
                'prestasi' => $this->statusPrestasi(),
            ],
        ]);
    }

    private function prestasiMenunggu(): Builder
    {
        return PengajuanPrestasi::where('status', PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI);
    }

    private function arsipMenunggu(): Builder
    {
        return PengajuanPrestasi::where('status', PengajuanPrestasi::STATUS_DISETUJUI)
            ->where('status_arsip', PengajuanPrestasi::STATUS_ARSIP_MENUNGGU);
    }

    /**
     * Antrean gabungan usulan lomba + prestasi + arsip, yang paling lama menunggu di atas.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function antrean(): Collection
    {
        $usulan = UsulanLomba::whereIn('status', ['menunggu', 'diproses'])
            ->orderBy('created_at')->limit(self::BATAS_ANTREAN)->get()
            ->map(fn (UsulanLomba $u): array => [
                'kode' => $u->nomor.' · '.$u->judul_lomba,
                'jenis' => 'Usulan lomba',
                'diterima' => $u->created_at,
                'status' => $u->status_label,
                'badge' => $u->status,
                'aksi' => 'Tinjau',
                'url' => route('admin.usulan.show', $u),
            ]);

        $prestasi = $this->prestasiMenunggu()
            ->orderBy('created_at')->limit(self::BATAS_ANTREAN)->get()
            ->map(fn (PengajuanPrestasi $p): array => [
                'kode' => $p->nomor_pengajuan.' · '.$p->judul_arsip,
                'jenis' => 'Prestasi & Arsip',
                'diterima' => $p->created_at,
                'status' => 'Menunggu Verifikasi',
                'badge' => 'menunggu',
                'aksi' => 'Verifikasi',
                'url' => route('admin.verifikasi.show', $p),
            ]);

        $arsip = $this->arsipMenunggu()
            ->orderBy('created_at')->limit(self::BATAS_ANTREAN)->get()
            ->map(fn (PengajuanPrestasi $p): array => [
                'kode' => $p->nomor_pengajuan.' · '.$p->judul_arsip,
                'jenis' => 'Prestasi & Arsip',
                'diterima' => $p->created_at,
                'status' => 'Menunggu Moderasi',
                'badge' => 'menunggu',
                'aksi' => 'Moderasi',
                'url' => route('admin.verifikasi.show', $p),
            ]);

        return collect()->merge($usulan)->merge($prestasi)->merge($arsip)
            ->sortBy(fn (array $baris): int => $baris['diterima']->getTimestamp())
            ->take(self::BATAS_ANTREAN)
            ->values();
    }

    /**
     * Grafik 1: pengajuan masuk per bulan (usulan lomba dan prestasi/arsip), enam bulan terakhir.
     * Menggabungkan dua tabel: usulan_lomba dan pengajuan_prestasi.
     *
     * @return array{labels: list<string>, series: list<array<string, mixed>>}
     */
    private function trenPengajuan(): array
    {
        $bulanIni = Carbon::now(self::ZONA)->startOfMonth();
        $bulan = collect(range(5, 0))->map(fn (int $i): Carbon => $bulanIni->copy()->subMonths($i));
        $awal = $bulan->first()->copy()->setTimezone(config('app.timezone'));

        $hitung = fn (Collection $tanggal): array => $bulan->map(
            fn (Carbon $b): int => $tanggal->filter(
                fn ($t): bool => Carbon::parse($t)->setTimezone(self::ZONA)->format('Y-m') === $b->format('Y-m')
            )->count()
        )->all();

        $usulan = UsulanLomba::where('created_at', '>=', $awal)->pluck('created_at');
        $prestasi = PengajuanPrestasi::where('created_at', '>=', $awal)->pluck('created_at');

        return [
            'labels' => $bulan->map(fn (Carbon $b): string => $b->copy()->locale('id')->translatedFormat('M'))->all(),
            'series' => [
                ['name' => 'Usulan lomba', 'color' => '#2563eb', 'values' => $hitung($usulan)],
                ['name' => 'Prestasi & arsip', 'color' => '#16a34a', 'values' => $hitung($prestasi)],
            ],
        ];
    }

    /**
     * Grafik 2: usulan lomba menurut status.
     *
     * @return list<array{label: string, value: int, color: string}>
     */
    private function statusUsulan(): array
    {
        $jumlah = UsulanLomba::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            ['label' => 'Menunggu', 'value' => (int) ($jumlah['menunggu'] ?? 0), 'color' => '#d97706'],
            ['label' => 'Diproses', 'value' => (int) ($jumlah['diproses'] ?? 0), 'color' => '#2563eb'],
            ['label' => 'Dipublikasikan', 'value' => (int) ($jumlah['dipublikasikan'] ?? 0), 'color' => '#16a34a'],
            ['label' => 'Ditolak', 'value' => (int) ($jumlah['ditolak'] ?? 0), 'color' => '#dc2626'],
        ];
    }

    /**
     * Grafik 3: lomba tayang per kategori, dipisah berdasarkan asalnya.
     * Menggabungkan tabel lomba dengan usulan_lomba (LEFT JOIN).
     *
     * @return array{labels: list<string>, series: list<array<string, mixed>>}
     */
    private function lombaPerKategori(): array
    {
        $baris = DB::table('lomba')
            ->leftJoin('usulan_lomba', 'usulan_lomba.lomba_id', '=', 'lomba.lomba_id')
            ->where('lomba.status_publikasi', Lomba::STATUS_TAYANG)
            ->selectRaw('lomba.kategori as kategori, COUNT(DISTINCT lomba.lomba_id) as total, COUNT(DISTINCT usulan_lomba.lomba_id) as dari_usulan')
            ->groupBy('lomba.kategori')
            ->orderBy('lomba.kategori')
            ->get();

        return [
            'labels' => $baris->pluck('kategori')->all(),
            'series' => [
                ['name' => 'Dari usulan mahasiswa', 'color' => '#2563eb', 'values' => $baris->map(fn (object $b): int => (int) $b->dari_usulan)->all()],
                ['name' => 'Dikelola langsung admin', 'color' => '#94a3b8', 'values' => $baris->map(fn (object $b): int => (int) $b->total - (int) $b->dari_usulan)->all()],
            ],
        ];
    }

    /**
     * Grafik 4: pengajuan prestasi dan arsip menurut tahap (saling lepas, jumlahnya = seluruh pengajuan).
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    private function statusPrestasi(): array
    {
        $baris = PengajuanPrestasi::selectRaw('status, status_arsip, COUNT(*) as total')
            ->groupBy('status', 'status_arsip')->get();

        $hitung = fn (callable $syarat): int => (int) $baris->filter($syarat)->sum('total');

        return [
            'labels' => ['Menunggu verifikasi', 'Arsip menunggu moderasi', 'Disetujui', 'Ditolak'],
            'values' => [
                $hitung(fn ($b): bool => $b->status === PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI),
                $hitung(fn ($b): bool => $b->status === PengajuanPrestasi::STATUS_DISETUJUI
                    && $b->status_arsip === PengajuanPrestasi::STATUS_ARSIP_MENUNGGU),
                $hitung(fn ($b): bool => $b->status === PengajuanPrestasi::STATUS_DISETUJUI
                    && $b->status_arsip !== PengajuanPrestasi::STATUS_ARSIP_MENUNGGU),
                $hitung(fn ($b): bool => $b->status === PengajuanPrestasi::STATUS_DITOLAK),
            ],
        ];
    }
}
