<?php

namespace App\Http\Controllers;

use App\Models\Bimbingan;
use App\Models\Konsultasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Konsultasi = bagian dari bimbingan.
 * - Mahasiswa: kirim konsultasi dari halaman detail bimbingannya (bimbingan.show).
 * - Dosen: menanggapi atau menolak dari menu "Permintaan Konsultasi".
 */
class KonsultasiController extends Controller
{
    // Mahasiswa: kirim konsultasi ke pembimbing
    public function store(Request $request, Bimbingan $bimbingan): RedirectResponse
    {
        $this->pastikanRole($request, ['mahasiswa']);
        abort_unless((int) $bimbingan->mahasiswa_id === (int) $request->user()->id, 403);

        $data = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($bimbingan, $data) {
            // Kunci baris bimbingan supaya klik ganda tidak meloloskan dua konsultasi sekaligus.
            $b = Bimbingan::whereKey($bimbingan->getKey())->lockForUpdate()->firstOrFail();

            if ($b->status !== Bimbingan::DISETUJUI) {
                throw ValidationException::withMessages([
                    'pertanyaan' => 'Konsultasi hanya bisa diajukan setelah bimbinganmu disetujui dosen.',
                ]);
            }

            $masihMenunggu = Konsultasi::where('bimbingan_id', $b->getKey())
                ->where('status', Konsultasi::MENUNGGU)
                ->exists();

            if ($masihMenunggu) {
                throw ValidationException::withMessages([
                    'pertanyaan' => 'Konsultasimu sebelumnya belum dijawab dosen. Tunggu sampai ditanggapi atau ditolak.',
                ]);
            }

            Konsultasi::create([
                'bimbingan_id' => $b->getKey(),
                'mahasiswa_id' => $b->mahasiswa_id,
                'dosen_id' => $b->dosen_id, // selalu ke pembimbing
                'pertanyaan' => $data['pertanyaan'],
                'status' => Konsultasi::MENUNGGU,
            ]);
        });

        return redirect()->route('bimbingan.show', $bimbingan)
            ->with('success', 'Konsultasi terkirim ke pembimbing. Kamu bisa mengirim konsultasi berikutnya setelah dijawab.');
    }

    // Dosen: daftar konsultasi dari mahasiswa bimbingannya (tab status + pencarian)
    public function dosenIndex(Request $request): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);

        $status = in_array($request->query('status'), Konsultasi::STATUSES, true)
            ? $request->query('status')
            : Konsultasi::MENUNGGU;
        $q = trim((string) $request->query('q'));

        $base = Konsultasi::where('dosen_id', $request->user()->id);

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $items = (clone $base)
            ->with(['mahasiswa', 'bimbingan'])
            ->where('status', $status)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('pertanyaan', 'like', "%{$q}%")
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('bimbingan', fn ($b) => $b->where('nama_tim', 'like', "%{$q}%")
                            ->orWhere('topik_judul', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->latest('konsultasi_id')
            ->get();

        return view('konsultasi.dosen-index', compact('items', 'status', 'q', 'counts'));
    }

    // Dosen: detail satu konsultasi + form tanggapan
    public function dosenShow(Request $request, Konsultasi $konsultasi): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        $this->pastikanPemilik($request, $konsultasi);

        return view('konsultasi.dosen-show', [
            'item' => $konsultasi->load(['mahasiswa', 'dosen', 'bimbingan']),
        ]);
    }

    // Dosen: tanggapi atau tolak (catatan wajib untuk keduanya)
    public function dosenRespond(Request $request, Konsultasi $konsultasi): RedirectResponse
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        $this->pastikanPemilik($request, $konsultasi);

        $data = $request->validate([
            'aksi' => ['required', 'in:tanggapi,tolak'],
            'catatan' => ['required', 'string', 'max:2000'],
        ], [
            'catatan.required' => 'Tanggapan atau alasan penolakan wajib diisi.',
        ]);

        // Update kondisional: hanya berhasil kalau masih menunggu.
        $berhasil = Konsultasi::whereKey($konsultasi->getKey())
            ->where('dosen_id', $request->user()->id)
            ->where('status', Konsultasi::MENUNGGU)
            ->update([
                'status' => $data['aksi'] === 'tanggapi' ? Konsultasi::DITANGGAPI : Konsultasi::DITOLAK,
                'jawaban_arahan' => $data['catatan'],
                'responded_at' => now(),
                'updated_at' => now(),
            ]);

        if (! $berhasil) {
            return redirect()->route('dosen.konsultasi.show', $konsultasi)
                ->with('error', 'Konsultasi ini sudah diproses.');
        }

        return redirect()->route('dosen.konsultasi.show', $konsultasi)
            ->with('success', $data['aksi'] === 'tanggapi'
                ? 'Tanggapan berhasil dikirim.'
                : 'Konsultasi ditolak. Alasan sudah tercatat.');
    }

    private function pastikanRole(Request $request, array $roles): void
    {
        abort_unless($request->user() && in_array($request->user()->role, $roles, true), 403);
    }

    private function pastikanPemilik(Request $request, Konsultasi $konsultasi): void
    {
        abort_unless((int) $konsultasi->dosen_id === (int) $request->user()->id, 403);
    }
}
