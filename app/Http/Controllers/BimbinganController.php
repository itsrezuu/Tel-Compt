<?php

namespace App\Http\Controllers;

use App\Models\Bimbingan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BimbinganController extends Controller
{
    // Mahasiswa: daftar pengajuan miliknya
    public function index(Request $request): View
    {
        $this->pastikanRole($request, ['mahasiswa']);
        Bimbingan::tolakKadaluarsa();

        $items = Bimbingan::with('dosen')
            ->where('mahasiswa_id', $request->user()->id)
            ->latest()
            ->latest('bimbingan_id')
            ->get();

        $aktif = $items->contains(fn ($i) => in_array($i->status, [Bimbingan::MENUNGGU, Bimbingan::DISETUJUI], true));

        return view('bimbingan.index', compact('items', 'aktif'));
    }

    // Mahasiswa: detail satu bimbingan + konsultasi dengan pembimbing
    public function show(Request $request, Bimbingan $bimbingan): View
    {
        $this->pastikanRole($request, ['mahasiswa']);
        abort_unless((int) $bimbingan->mahasiswa_id === (int) $request->user()->id, 403);
        Bimbingan::tolakKadaluarsa();

        $item = $bimbingan->fresh(['dosen', 'mahasiswa']);
        $daftarKonsultasi = $item->konsultasi()->latest()->latest('konsultasi_id')->get();

        // Konsultasi hanya untuk bimbingan yang disetujui, dan hanya kalau
        // konsultasi sebelumnya sudah dijawab (ditanggapi atau ditolak).
        $bisaKonsultasi = $item->status === Bimbingan::DISETUJUI
            && ! $daftarKonsultasi->contains('status', 'menunggu');

        return view('bimbingan.show', compact('item', 'daftarKonsultasi', 'bisaKonsultasi'));
    }

    // Mahasiswa: form pengajuan (diblokir kalau masih ada pengajuan aktif)
    public function create(Request $request): View|RedirectResponse
    {
        $this->pastikanRole($request, ['mahasiswa']);
        Bimbingan::tolakKadaluarsa();

        if ($this->punyaPengajuanAktif($request->user()->id)) {
            return redirect()->route('bimbingan.index')
                ->with('error', 'Kamu masih punya pengajuan bimbingan yang aktif. Tunggu sampai ditolak sebelum mengajukan lagi.');
        }

        $user = $request->user();
        $dosenList = User::whereIn('role', ['dosen', 'kaprodi'])->orderBy('name')->get();
        $lombaOptions = $this->lombaOptions();
        $ketuaDefault = trim($user->name.($user->nim_nip ? ' · '.$user->nim_nip : ''));

        return view('bimbingan.create', compact('dosenList', 'lombaOptions', 'ketuaDefault'));
    }

    // Mahasiswa: simpan pengajuan
    public function store(Request $request): RedirectResponse
    {
        $this->pastikanRole($request, ['mahasiswa']);

        $lombaOptions = $this->lombaOptions();

        $data = $request->validate([
            'lomba_id' => [$lombaOptions ? 'required' : 'nullable', Rule::in(array_keys($lombaOptions))],
            'dosen_id' => ['required', Rule::exists('users', 'id')->whereIn('role', ['dosen', 'kaprodi'])],
            'topik_judul' => ['required', 'string', 'max:150'],
            'ringkasan_kebutuhan' => ['required', 'string', 'max:1500'],
            'nama_tim' => ['required', 'string', 'max:100'],
            'ketua_tim' => ['required', 'string', 'max:100'],
            'anggota_tim' => ['required', 'string', 'max:1000'],
            'rencana_kegiatan' => ['required', 'string', 'max:1500'],
        ]);

        $baru = DB::transaction(function () use ($request, $data) {
            Bimbingan::tolakKadaluarsa();

            // Kunci baris user supaya klik ganda tidak meloloskan dua pengajuan sekaligus.
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();

            if ($this->punyaPengajuanAktif($user->id)) {
                throw ValidationException::withMessages([
                    'dosen_id' => 'Kamu masih punya pengajuan bimbingan yang aktif. Tunggu sampai ditolak sebelum mengajukan lagi.',
                ]);
            }

            return Bimbingan::create($data + [
                'mahasiswa_id' => $user->id,
                'status' => Bimbingan::MENUNGGU,
                'batas_respons_at' => now()->addHours(Bimbingan::BATAS_RESPONS_JAM),
            ]);
        });

        $dosen = User::find($baru->dosen_id);

        return redirect()->route('bimbingan.index')->with('terkirim', [
            'judul' => 'Permintaan terkirim · '.$baru->kode,
            'isi' => "{$baru->nama_tim} telah mengajukan bimbingan kepada {$dosen?->name}. Dosen punya waktu 1 hari untuk memutuskan; jika tidak, pengajuan ditolak otomatis.",
        ]);
    }

    // Dosen: daftar pengajuan yang ditujukan kepadanya (tab status + pencarian + ringkasan terpilih)
    public function dosenIndex(Request $request): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        Bimbingan::tolakKadaluarsa();

        $status = in_array($request->query('status'), Bimbingan::STATUSES, true)
            ? $request->query('status')
            : Bimbingan::MENUNGGU;
        $q = trim((string) $request->query('q'));

        $lombaIds = ($q !== '' && Schema::hasTable('lomba'))
            ? DB::table('lomba')->where('judul_lomba', 'like', "%{$q}%")->pluck('lomba_id')->all()
            : [];

        $base = Bimbingan::where('dosen_id', $request->user()->id);

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $items = (clone $base)
            ->with('mahasiswa')
            ->where('status', $status)
            ->when($q !== '', function ($query) use ($q, $lombaIds) {
                $query->where(function ($w) use ($q, $lombaIds) {
                    $w->where('topik_judul', 'like', "%{$q}%")
                        ->orWhere('nama_tim', 'like', "%{$q}%")
                        ->orWhere('ketua_tim', 'like', "%{$q}%")
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('name', 'like', "%{$q}%"));

                    if ($lombaIds) {
                        $w->orWhereIn('lomba_id', $lombaIds);
                    }
                });
            })
            ->latest()
            ->latest('bimbingan_id')
            ->get();

        // "Ringkasan terpilih": baris yang diklik (Tinjau detail), default baris pertama.
        $pilih = $items->firstWhere('bimbingan_id', (int) $request->query('pilih')) ?? $items->first();

        return view('bimbingan.dosen-index', compact('items', 'status', 'q', 'counts', 'pilih'));
    }

    // Dosen: detail satu pengajuan + keputusan
    public function dosenShow(Request $request, Bimbingan $bimbingan): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        $this->pastikanPemilik($request, $bimbingan);
        Bimbingan::tolakKadaluarsa();

        return view('bimbingan.dosen-show', ['item' => $bimbingan->fresh(['mahasiswa', 'dosen'])]);
    }

    // Dosen: setujui atau tolak (catatan keputusan wajib untuk keduanya)
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

        // Update kondisional: hanya berhasil kalau masih menunggu DAN belum lewat batas 1 hari.
        $berhasil = Bimbingan::whereKey($bimbingan->getKey())
            ->where('dosen_id', $request->user()->id)
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

        return redirect()->route('dosen.bimbingan.show', $bimbingan)
            ->with('success', $data['aksi'] === 'setuju'
                ? 'Pengajuan bimbingan disetujui.'
                : 'Pengajuan bimbingan ditolak. Alasan sudah tercatat.');
    }

    private function punyaPengajuanAktif(int $mahasiswaId): bool
    {
        return Bimbingan::where('mahasiswa_id', $mahasiswaId)->aktif()->exists();
    }

    /**
     * Pilihan lomba untuk form. Tabel lomba (SCRUM-23) mungkin belum ada;
     * kalau belum ada, hasilnya kosong dan kolom lomba disembunyikan.
     *
     * @return array<int, string> [lomba_id => judul_lomba]
     */
    private function lombaOptions(): array
    {
        if (! Schema::hasTable('lomba')) {
            return [];
        }

        return DB::table('lomba')
            ->where('status_publikasi', 'dipublikasikan')
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
        abort_unless((int) $bimbingan->dosen_id === (int) $request->user()->id, 403);
    }
}
