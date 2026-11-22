<?php

namespace App\Http\Controllers;

use App\Models\Bimbingan;
use App\Models\Konsultasi;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KonsultasiController extends Controller
{
    public function store(Request $request, Bimbingan $bimbingan): RedirectResponse
    {
        $this->pastikanRole($request, ['mahasiswa']);
        abort_unless((int) $bimbingan->mahasiswa_id === (int) $request->user()->getKey(), 403);

        $data = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:1000'],
        ]);

        $konsultasi = DB::transaction(function () use ($bimbingan, $data): Konsultasi {
            $pengajuan = Bimbingan::query()
                ->whereKey($bimbingan->getKey())
                ->whereNull('archived_at')
                ->lockForUpdate()
                ->firstOrFail();

            if ($pengajuan->status !== Bimbingan::DISETUJUI) {
                throw ValidationException::withMessages([
                    'pertanyaan' => 'Log bimbingan hanya bisa dikirim setelah pengajuan disetujui dosen.',
                ]);
            }

            $masihMenunggu = Konsultasi::query()
                ->where('bimbingan_id', $pengajuan->getKey())
                ->where('status', Konsultasi::MENUNGGU)
                ->exists();

            if ($masihMenunggu) {
                throw ValidationException::withMessages([
                    'pertanyaan' => 'Log sebelumnya masih menunggu keputusan dosen.',
                ]);
            }

            $terakhir = Konsultasi::query()
                ->where('bimbingan_id', $pengajuan->getKey())
                ->latest('konsultasi_id')
                ->first();

            if ($terakhir?->status === Konsultasi::DITOLAK) {
                throw ValidationException::withMessages([
                    'pertanyaan' => 'Perbaiki log yang ditolak pada baris yang sama sebelum mengirim log baru.',
                ]);
            }

            return Konsultasi::create([
                'bimbingan_id' => $pengajuan->getKey(),
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'dosen_id' => $pengajuan->dosen_id,
                'pertanyaan' => $data['pertanyaan'],
                'status' => Konsultasi::MENUNGGU,
            ]);
        });

        $this->notifyDosen($konsultasi, 'Log bimbingan baru', 'mengirim log');

        return redirect()->route('bimbingan.show', $bimbingan)
            ->with('success', 'Log bimbingan terkirim kepada dosen.');
    }

    public function revise(Request $request, Bimbingan $bimbingan, Konsultasi $konsultasi): RedirectResponse
    {
        $this->pastikanRole($request, ['mahasiswa']);
        abort_unless((int) $bimbingan->mahasiswa_id === (int) $request->user()->getKey(), 403);

        $data = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:1000'],
        ]);

        $log = DB::transaction(function () use ($request, $bimbingan, $konsultasi, $data): Konsultasi {
            $pengajuan = Bimbingan::query()
                ->whereKey($bimbingan->getKey())
                ->where('mahasiswa_id', $request->user()->getKey())
                ->whereNull('archived_at')
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($pengajuan->status, [Bimbingan::DISETUJUI, Bimbingan::SELESAI], true)) {
                throw ValidationException::withMessages([
                    'pertanyaan' => 'Log hanya dapat diperbaiki pada bimbingan yang telah disetujui.',
                ]);
            }

            $log = Konsultasi::query()
                ->whereKey($konsultasi->getKey())
                ->where('bimbingan_id', $pengajuan->getKey())
                ->where('mahasiswa_id', $request->user()->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $idLogTerakhir = Konsultasi::query()
                ->where('bimbingan_id', $pengajuan->getKey())
                ->latest('konsultasi_id')
                ->value('konsultasi_id');

            if ($log->status !== Konsultasi::DITOLAK || (int) $idLogTerakhir !== (int) $log->getKey()) {
                throw ValidationException::withMessages([
                    'pertanyaan' => 'Log ini tidak lagi menunggu perbaikan.',
                ]);
            }

            $log->update([
                'pertanyaan' => $data['pertanyaan'],
                'status' => Konsultasi::MENUNGGU,
                'jawaban_arahan' => null,
                'responded_at' => null,
            ]);

            return $log->refresh();
        });

        $this->notifyDosen($log, 'Log bimbingan diperbaiki', 'memperbaiki log');

        return redirect()->route('bimbingan.show', $bimbingan)
            ->with('success', 'Log yang sama berhasil diperbaiki dan dikirim ulang.');
    }

    public function dosenIndex(Request $request): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);

        $status = in_array($request->query('status'), Konsultasi::STATUSES, true)
            ? $request->query('status')
            : Konsultasi::MENUNGGU;
        $q = trim((string) $request->query('q'));

        $base = Konsultasi::query()
            ->where('dosen_id', $request->user()->getKey())
            ->whereHas('bimbingan', fn ($query) => $query->whereNull('archived_at'));

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $items = (clone $base)
            ->with(['mahasiswa', 'bimbingan'])
            ->where('status', $status)
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($where) use ($q): void {
                    $where->where('pertanyaan', 'like', '%'.$q.'%')
                        ->orWhereHas('mahasiswa', fn ($mahasiswa) => $mahasiswa->where('name', 'like', '%'.$q.'%'))
                        ->orWhereHas('bimbingan', fn ($bimbingan) => $bimbingan
                            ->where('nama_tim', 'like', '%'.$q.'%')
                            ->orWhere('topik_judul', 'like', '%'.$q.'%'));
                });
            })
            ->latest()
            ->latest('konsultasi_id')
            ->get();

        return view('konsultasi.dosen-index', compact('items', 'status', 'q', 'counts'));
    }

    public function dosenShow(Request $request, Konsultasi $konsultasi): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        $this->pastikanPemilik($request, $konsultasi);

        return view('konsultasi.dosen-show', [
            'item' => $konsultasi->load(['mahasiswa', 'dosen', 'bimbingan']),
        ]);
    }

    public function dosenUpdate(Request $request, Konsultasi $konsultasi): RedirectResponse
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        $this->pastikanPemilik($request, $konsultasi);
        abort_unless($konsultasi->bimbingan()->whereNull('archived_at')->exists(), 404);

        $data = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:1000'],
        ]);

        $berhasil = DB::transaction(function () use ($request, $konsultasi, $data): bool {
            $log = Konsultasi::query()
                ->whereKey($konsultasi->getKey())
                ->where('dosen_id', $request->user()->getKey())
                ->where('status', Konsultasi::MENUNGGU)
                ->whereHas('bimbingan', fn ($query) => $query->whereNull('archived_at'))
                ->lockForUpdate()
                ->first();

            if ($log === null) {
                return false;
            }

            $log->update(['pertanyaan' => $data['pertanyaan']]);

            return true;
        });

        if (! $berhasil) {
            return redirect()->route('dosen.konsultasi.show', $konsultasi)
                ->with('error', 'Log hanya dapat diubah selama menunggu keputusan dosen.');
        }

        return redirect()->route('dosen.konsultasi.show', $konsultasi)
            ->with('success', 'Isi log bimbingan berhasil diperbarui.');
    }

    public function dosenRespond(Request $request, Konsultasi $konsultasi): RedirectResponse
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);
        $this->pastikanPemilik($request, $konsultasi);
        abort_unless($konsultasi->bimbingan()->whereNull('archived_at')->exists(), 404);

        $data = $request->validate([
            'aksi' => ['required', 'in:tanggapi,tolak'],
            'catatan' => ['required', 'string', 'max:2000'],
        ], [
            'catatan.required' => 'Tanggapan atau alasan penolakan wajib diisi.',
        ]);

        $berhasil = Konsultasi::query()
            ->whereKey($konsultasi->getKey())
            ->where('dosen_id', $request->user()->getKey())
            ->where('status', Konsultasi::MENUNGGU)
            ->update([
                'status' => $data['aksi'] === 'tanggapi' ? Konsultasi::DITANGGAPI : Konsultasi::DITOLAK,
                'jawaban_arahan' => $data['catatan'],
                'responded_at' => now(),
                'updated_at' => now(),
            ]);

        if (! $berhasil) {
            return redirect()->route('dosen.konsultasi.show', $konsultasi)
                ->with('error', 'Log ini sudah diproses.');
        }

        $statusText = $data['aksi'] === 'tanggapi' ? 'disetujui' : 'ditolak';
        User::find($konsultasi->mahasiswa_id)?->notify(new ActivityNotification(
            $data['aksi'] === 'tanggapi' ? 'Log bimbingan disetujui' : 'Perbaiki log bimbingan',
            "Log {$konsultasi->kode} telah {$statusText}. Catatan dosen: {$data['catatan']}",
            'konsultasi_status',
            (int) $konsultasi->getKey(),
            route('bimbingan.show', $konsultasi->bimbingan_id),
        ));

        return redirect()->route('dosen.konsultasi.show', $konsultasi)
            ->with('success', $data['aksi'] === 'tanggapi'
                ? 'Log bimbingan disetujui.'
                : 'Log ditolak dan dikirim kembali kepada mahasiswa untuk diperbaiki.');
    }

    private function notifyDosen(Konsultasi $konsultasi, string $title, string $activity): void
    {
        $konsultasi->loadMissing('mahasiswa');

        User::find($konsultasi->dosen_id)?->notify(new ActivityNotification(
            $title,
            "{$konsultasi->mahasiswa?->name} {$activity} {$konsultasi->kode}.",
            'konsultasi_dibuat',
            (int) $konsultasi->getKey(),
            route('dosen.konsultasi.show', $konsultasi),
        ));
    }

    private function pastikanRole(Request $request, array $roles): void
    {
        abort_unless($request->user() && in_array($request->user()->role, $roles, true), 403);
    }

    private function pastikanPemilik(Request $request, Konsultasi $konsultasi): void
    {
        abort_unless((int) $konsultasi->dosen_id === (int) $request->user()->getKey(), 403);
    }
}
