<?php

namespace App\Http\Controllers;

use App\Models\Bimbingan;
use App\Models\Konsultasi;
use App\Models\Lomba;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BimbinganController extends Controller
{
    public function index(Request $request): View
    {
        $this->pastikanRole($request, ['mahasiswa']);
        Bimbingan::tolakKadaluarsa();

        $mahasiswaId = (int) $request->user()->getKey();
        $items = Bimbingan::with(['dosen', 'lomba'])
            ->where('mahasiswa_id', $mahasiswaId)
            ->latest()
            ->latest('bimbingan_id')
            ->get();
        $adaLombaTersedia = $this->lombaOptions($mahasiswaId) !== [];

        return view('bimbingan.index', compact('items', 'adaLombaTersedia'));
    }

    public function show(Request $request, Bimbingan $bimbingan): View
    {
        $this->pastikanRole($request, ['mahasiswa']);
        abort_unless((int) $bimbingan->mahasiswa_id === (int) $request->user()->getKey(), 403);
        Bimbingan::tolakKadaluarsa();

        $item = $bimbingan->fresh(['dosen', 'mahasiswa', 'lomba']);
        $daftarKonsultasi = $item->konsultasi()
            ->latest('konsultasi_id')
            ->get();
        $konsultasiTerakhir = $daftarKonsultasi->first();
        $konsultasiPerluRevisi = $konsultasiTerakhir?->status === Konsultasi::DITOLAK
            ? $konsultasiTerakhir
            : null;
        $bisaKonsultasi = $item->archived_at === null
            && $item->status === Bimbingan::DISETUJUI
            && ! in_array($konsultasiTerakhir?->status, [Konsultasi::MENUNGGU, Konsultasi::DITOLAK], true);
        $dosenList = $item->status === Bimbingan::DITOLAK && $item->archived_at === null
            ? User::query()
                ->whereIn('role', ['dosen', 'kaprodi'])
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('bimbingan.show', compact(
            'item',
            'daftarKonsultasi',
            'bisaKonsultasi',
            'konsultasiPerluRevisi',
            'dosenList',
        ));
    }

    public function create(Request $request): View
    {
        $this->pastikanRole($request, ['mahasiswa']);
        Bimbingan::tolakKadaluarsa();

        $user = $request->user();
        $dosenList = User::query()
            ->whereIn('role', ['dosen', 'kaprodi'])
            ->orderBy('name')
            ->get(['id', 'name']);
        $lombaOptions = $this->lombaOptions((int) $user->getKey());
        $ketuaDefault = trim($user->name.($user->nim_nip ? ' · '.$user->nim_nip : ''));
        $oldMemberIds = array_filter(
            (array) old('anggota_tim_ids', []),
            static fn (mixed $id): bool => is_scalar($id) && filter_var($id, FILTER_VALIDATE_INT) !== false,
        );
        $oldMemberIds = array_values(array_unique(array_map('intval', $oldMemberIds)));
        $mahasiswaById = User::query()
            ->whereIn('id', $oldMemberIds)
            ->where('role', 'mahasiswa')
            ->get(['id', 'name', 'nim_nip'])
            ->keyBy('id');
        $anggotaTerpilih = collect($oldMemberIds)
            ->map(fn (int $id): ?User => $mahasiswaById->get($id))
            ->filter()
            ->values();

        return view('bimbingan.create', compact('dosenList', 'lombaOptions', 'ketuaDefault', 'anggotaTerpilih'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanRole($request, ['mahasiswa']);

        $data = $request->validate([
            'lomba_id' => [
                'required',
                'integer',
                Rule::exists('lomba', 'lomba_id')->where('status_publikasi', 'dipublikasikan'),
            ],
            'dosen_id' => ['required', Rule::exists('users', 'id')->whereIn('role', ['dosen', 'kaprodi'])],
            'topik_judul' => ['required', 'string', 'max:150'],
            'ringkasan_kebutuhan' => ['required', 'string', 'max:1500'],
            'nama_tim' => ['required', 'string', 'max:100'],
            'ketua_tim' => ['required', 'string', 'max:100'],
            'anggota_tim_ids' => ['required', 'array', 'min:1'],
            'anggota_tim_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where('role', 'mahasiswa'),
            ],
            'rencana_kegiatan' => ['required', 'string', 'max:1500'],
        ], [
            'lomba_id.required' => 'Pilih lomba yang akan diikuti.',
            'lomba_id.exists' => 'Lomba tidak tersedia untuk pengajuan bimbingan.',
            'anggota_tim_ids.required' => 'Pilih minimal satu anggota tim.',
            'anggota_tim_ids.min' => 'Pilih minimal satu anggota tim.',
            'anggota_tim_ids.*.distinct' => 'Anggota tim tidak boleh dipilih lebih dari sekali.',
            'anggota_tim_ids.*.exists' => 'Pilih akun mahasiswa yang terdaftar sebagai anggota tim.',
        ]);
        $anggotaTimIds = array_map('intval', $data['anggota_tim_ids']);
        unset($data['anggota_tim_ids']);

        $baru = DB::transaction(function () use ($request, $data, $anggotaTimIds): Bimbingan {
            Bimbingan::tolakKadaluarsa();

            $user = User::query()
                ->whereKey($request->user()->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $anggotaMahasiswa = User::query()
                ->whereIn('id', $anggotaTimIds)
                ->where('role', 'mahasiswa')
                ->lockForUpdate()
                ->get(['id', 'name', 'nim_nip'])
                ->keyBy('id');

            if ($anggotaMahasiswa->count() !== count($anggotaTimIds)) {
                throw ValidationException::withMessages([
                    'anggota_tim_ids' => 'Pilih akun mahasiswa yang terdaftar sebagai anggota tim.',
                ]);
            }

            $anggotaTim = collect($anggotaTimIds)
                ->map(function (int $id) use ($anggotaMahasiswa): string {
                    $mahasiswa = $anggotaMahasiswa->get($id);

                    return $mahasiswa->name.($mahasiswa->nim_nip ? ' · '.$mahasiswa->nim_nip : '');
                })
                ->implode("\n");

            if (mb_strlen($anggotaTim) > 1000) {
                throw ValidationException::withMessages([
                    'anggota_tim_ids' => 'Informasi anggota tim tidak boleh lebih dari 1000 karakter.',
                ]);
            }

            $data['anggota_tim'] = $anggotaTim;

            $lombaTersedia = Lomba::query()
                ->whereKey($data['lomba_id'])
                ->where('status_publikasi', 'dipublikasikan')
                ->lockForUpdate()
                ->exists();

            if (! $lombaTersedia) {
                throw ValidationException::withMessages([
                    'lomba_id' => 'Lomba tidak tersedia untuk pengajuan bimbingan.',
                ]);
            }

            $sudahAdaPengajuan = Bimbingan::query()
                ->where('mahasiswa_id', $user->getKey())
                ->where('lomba_id', $data['lomba_id'])
                ->whereNull('archived_at')
                ->lockForUpdate()
                ->exists();

            if ($sudahAdaPengajuan) {
                throw ValidationException::withMessages([
                    'lomba_id' => 'Kamu sudah memiliki pengajuan untuk lomba ini. Pengajuan yang ditolak dapat diajukan ulang dari halaman detailnya.',
                ]);
            }

            $baru = Bimbingan::create($data + [
                'mahasiswa_id' => $user->getKey(),
                'status' => Bimbingan::MENUNGGU,
                'batas_respons_at' => now()->addHours(Bimbingan::BATAS_RESPONS_JAM),
            ]);
            $baru->anggotaTim()->sync($anggotaTimIds);

            return $baru;
        });

        User::find($baru->dosen_id)?->notify(new ActivityNotification(
            'Permintaan bimbingan baru',
            "{$baru->nama_tim} mengajukan bimbingan untuk lomba {$baru->lomba_judul} dengan topik {$baru->topik_judul}.",
            'bimbingan_dibuat',
            (int) $baru->getKey(),
            route('dosen.bimbingan.show', $baru),
        ));

        return redirect()->route('bimbingan.index')->with('terkirim', [
            'judul' => 'Permintaan terkirim · '.$baru->kode,
            'isi' => "{$baru->nama_tim} telah mengajukan bimbingan kepada {$baru->dosen?->name}. Dosen punya waktu 1 hari untuk memutuskan; jika tidak, pengajuan ditolak otomatis.",
        ]);
    }

    public function cariMahasiswa(Request $request): JsonResponse
    {
        $this->pastikanRole($request, ['mahasiswa']);

        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 3) {
            return response()->json([]);
        }

        $mahasiswa = User::query()
            ->where('role', 'mahasiswa')
            ->where(function ($builder) use ($query): void {
                $builder->where('name', 'like', '%'.$query.'%')
                    ->orWhere('nim_nip', 'like', '%'.$query.'%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'nim_nip'])
            ->map(fn (User $user): array => [
                'id' => (int) $user->getKey(),
                'name' => $user->name,
                'nim_nip' => $user->nim_nip,
            ]);

        return response()->json($mahasiswa);
    }

    public function dosenIndex(Request $request): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        Bimbingan::tolakKadaluarsa();

        $status = in_array($request->query('status'), Bimbingan::STATUSES, true)
            ? $request->query('status')
            : Bimbingan::MENUNGGU;
        $q = trim((string) $request->query('q'));

        $lombaIds = $q !== ''
            ? Lomba::query()->where('judul_lomba', 'like', '%'.$q.'%')->pluck('lomba_id')->all()
            : [];

        $base = Bimbingan::query()
            ->whereNull('archived_at')
            ->where('dosen_id', $request->user()->getKey());

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $items = (clone $base)
            ->with(['mahasiswa', 'lomba'])
            ->where('status', $status)
            ->when($q !== '', function ($query) use ($q, $lombaIds): void {
                $query->where(function ($where) use ($q, $lombaIds): void {
                    $where->where('topik_judul', 'like', '%'.$q.'%')
                        ->orWhere('nama_tim', 'like', '%'.$q.'%')
                        ->orWhere('ketua_tim', 'like', '%'.$q.'%')
                        ->orWhereHas('mahasiswa', fn ($mahasiswa) => $mahasiswa->where('name', 'like', '%'.$q.'%'));

                    if ($lombaIds !== []) {
                        $where->orWhereIn('lomba_id', $lombaIds);
                    }
                });
            })
            ->latest()
            ->latest('bimbingan_id')
            ->get();

        $pilih = $items->firstWhere('bimbingan_id', (int) $request->query('pilih')) ?? $items->first();

        return view('bimbingan.dosen-index', compact('items', 'status', 'q', 'counts', 'pilih'));
    }

    public function dosenShow(Request $request, Bimbingan $bimbingan): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        $this->pastikanPemilik($request, $bimbingan);
        Bimbingan::tolakKadaluarsa();

        return view('bimbingan.dosen-show', [
            'item' => $bimbingan->fresh(['mahasiswa', 'dosen', 'lomba']),
        ]);
    }

    public function dosenRespond(Request $request, Bimbingan $bimbingan): RedirectResponse
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        $this->pastikanPemilik($request, $bimbingan);

        $data = $request->validate([
            'aksi' => ['required', 'in:setuju,tolak'],
            'catatan' => ['required', 'string', 'max:2000'],
        ], [
            'catatan.required' => 'Catatan keputusan atau alasan penolakan wajib diisi.',
        ]);

        $berhasil = Bimbingan::query()
            ->whereKey($bimbingan->getKey())
            ->whereNull('archived_at')
            ->where('dosen_id', $request->user()->getKey())
            ->where('status', Bimbingan::MENUNGGU)
            ->where('batas_respons_at', '>', now())
            ->update([
                'status' => $data['aksi'] === 'setuju' ? Bimbingan::DISETUJUI : Bimbingan::DITOLAK,
                'catatan_dosen' => $data['catatan'],
                'responded_at' => now(),
                'updated_at' => now(),
            ]);

        if (! $berhasil) {
            Bimbingan::tolakKadaluarsa();

            return redirect()->route('dosen.bimbingan.show', $bimbingan)
                ->with('error', 'Pengajuan sudah diproses atau melewati batas respons 1 hari.');
        }

        $statusText = $data['aksi'] === 'setuju' ? 'disetujui' : 'ditolak';
        User::find($bimbingan->mahasiswa_id)?->notify(new ActivityNotification(
            'Keputusan bimbingan',
            "Permohonan bimbingan {$bimbingan->kode} telah {$statusText}. Catatan: {$data['catatan']}",
            'bimbingan_status',
            (int) $bimbingan->getKey(),
            route('bimbingan.show', $bimbingan),
        ));

        return redirect()->route('dosen.bimbingan.show', $bimbingan)
            ->with('success', $data['aksi'] === 'setuju'
                ? 'Pengajuan bimbingan disetujui.'
                : 'Pengajuan bimbingan ditolak. Alasan sudah tercatat.');
    }

    public function dosenSelesaikan(Request $request, Bimbingan $bimbingan): RedirectResponse
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        $this->pastikanPemilik($request, $bimbingan);

        $berhasil = Bimbingan::query()
            ->whereKey($bimbingan->getKey())
            ->whereNull('archived_at')
            ->where('dosen_id', $request->user()->getKey())
            ->where('status', Bimbingan::DISETUJUI)
            ->update([
                'status' => Bimbingan::SELESAI,
                'updated_at' => now(),
            ]);

        if (! $berhasil) {
            return redirect()->route('dosen.bimbingan.show', $bimbingan)
                ->with('error', 'Bimbingan hanya bisa ditandai selesai setelah disetujui.');
        }

        User::find($bimbingan->mahasiswa_id)?->notify(new ActivityNotification(
            'Bimbingan selesai',
            "Bimbingan {$bimbingan->kode} telah ditandai selesai. Pengajuan untuk lomba lain tetap dapat dibuat.",
            'bimbingan_status',
            (int) $bimbingan->getKey(),
            route('bimbingan.show', $bimbingan),
        ));

        return redirect()->route('dosen.bimbingan.show', $bimbingan)
            ->with('success', 'Bimbingan ditandai selesai.');
    }

    public function ajukanUlang(Request $request, Bimbingan $bimbingan): RedirectResponse
    {
        $this->pastikanRole($request, ['mahasiswa']);
        abort_unless((int) $bimbingan->mahasiswa_id === (int) $request->user()->getKey(), 403);

        $data = $request->validate([
            'dosen_id' => ['required', Rule::exists('users', 'id')->whereIn('role', ['dosen', 'kaprodi'])],
        ]);

        $pengajuan = DB::transaction(function () use ($request, $bimbingan, $data): Bimbingan {
            $user = User::query()
                ->whereKey($request->user()->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $pengajuan = Bimbingan::query()
                ->whereKey($bimbingan->getKey())
                ->where('mahasiswa_id', $user->getKey())
                ->whereNull('archived_at')
                ->lockForUpdate()
                ->firstOrFail();

            if ($pengajuan->status !== Bimbingan::DITOLAK) {
                throw ValidationException::withMessages([
                    'dosen_id' => 'Hanya pengajuan yang ditolak yang dapat diajukan ulang.',
                ]);
            }

            $pengajuan->update([
                'dosen_id' => $data['dosen_id'],
                'status' => Bimbingan::MENUNGGU,
                'catatan_dosen' => null,
                'ditolak_otomatis' => false,
                'batas_respons_at' => now()->addHours(Bimbingan::BATAS_RESPONS_JAM),
                'responded_at' => null,
            ]);

            return $pengajuan->refresh();
        });

        User::find($pengajuan->dosen_id)?->notify(new ActivityNotification(
            'Permintaan bimbingan diajukan ulang',
            "{$pengajuan->nama_tim} mengajukan ulang bimbingan untuk lomba {$pengajuan->lomba_judul}.",
            'bimbingan_dibuat',
            (int) $pengajuan->getKey(),
            route('dosen.bimbingan.show', $pengajuan),
        ));

        return redirect()->route('bimbingan.show', $pengajuan)
            ->with('success', 'Pengajuan yang sama berhasil diajukan ulang kepada dosen pilihanmu.');
    }

    /**
     * @return array<int, string> [lomba_id => judul_lomba]
     */
    private function lombaOptions(int $mahasiswaId): array
    {
        $usedLombaIds = Bimbingan::query()
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereNull('archived_at')
            ->whereNotNull('lomba_id')
            ->pluck('lomba_id')
            ->all();

        return Lomba::query()
            ->where('status_publikasi', 'dipublikasikan')
            ->whereNotIn('lomba_id', $usedLombaIds)
            ->orderBy('judul_lomba')
            ->pluck('judul_lomba', 'lomba_id')
            ->all();
    }

    private function pastikanRole(Request $request, array $roles): void
    {
        abort_unless($request->user() && in_array($request->user()->role, $roles, true), 403);
    }

    private function pastikanPemilik(Request $request, Bimbingan $bimbingan): void
    {
        abort_unless((int) $bimbingan->dosen_id === (int) $request->user()->getKey(), 403);
    }
}
