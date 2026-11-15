<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanPrestasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PrestasiController extends Controller
{
    public function create(): View
    {
        $mahasiswa = $this->mahasiswa();
        $pengajuanTerbaru = null;

        if (session()->has('pengajuan_baru')) {
            $pengajuanTerbaru = $mahasiswa->prestasiDiajukan()
                ->whereKey((int) session('pengajuan_baru'))
                ->first();
        }

        return view('mahasiswa.prestasi.create', compact('pengajuanTerbaru'));
    }

    public function store(Request $request): RedirectResponse
    {
        $mahasiswa = $this->mahasiswa();

        $validated = $request->validate([
            'lomba' => ['required', 'string', 'max:255'],
            'prestasi_hasil' => ['required', 'string', 'max:255'],
            'tanggal_pencapaian' => ['required', 'date'],
            'peserta_tim' => ['required', 'string', 'max:255'],
            'bukti_pendukung' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'keluaran_lomba' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'judul_arsip' => ['required', 'string', 'max:255'],
            'pengalaman_proses' => ['required', 'string'],
            'pembelajaran_saran' => ['required', 'string'],
            'dokumen_pengalaman' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'persetujuan_publikasi' => ['required', 'accepted'],
        ], [
            'lomba.required' => 'Nama lomba wajib diisi.',
            'prestasi_hasil.required' => 'Prestasi atau hasil wajib diisi.',
            'tanggal_pencapaian.required' => 'Tanggal pencapaian wajib diisi.',
            'peserta_tim.required' => 'Peserta atau tim wajib diisi.',
            'bukti_pendukung.required' => 'Bukti pendukung wajib diunggah.',
            'bukti_pendukung.mimes' => 'Bukti pendukung harus berupa PDF, JPG, JPEG, atau PNG.',
            'bukti_pendukung.max' => 'Ukuran bukti pendukung maksimal 10 MB.',
            'keluaran_lomba.required' => 'Keluaran lomba wajib diunggah.',
            'keluaran_lomba.mimes' => 'Keluaran lomba harus berupa PDF, JPG, JPEG, atau PNG.',
            'keluaran_lomba.max' => 'Ukuran keluaran lomba maksimal 10 MB.',
            'judul_arsip.required' => 'Judul arsip wajib diisi.',
            'pengalaman_proses.required' => 'Pengalaman dan proses wajib diisi.',
            'pembelajaran_saran.required' => 'Pembelajaran dan saran wajib diisi.',
            'dokumen_pengalaman.mimes' => 'Dokumen pengalaman harus berupa PDF, JPG, JPEG, atau PNG.',
            'dokumen_pengalaman.max' => 'Ukuran dokumen pengalaman maksimal 10 MB.',
            'persetujuan_publikasi.accepted' => 'Persetujuan publikasi wajib dicentang.',
        ]);

        $fileFields = ['bukti_pendukung', 'keluaran_lomba', 'dokumen_pengalaman'];
        $storedPaths = [];

        try {
            foreach ($fileFields as $field) {
                if (! $request->hasFile($field)) {
                    continue;
                }

                $path = $request->file($field)->store('prestasi', 'local');

                if (! is_string($path) || $path === '') {
                    throw new RuntimeException('Berkas prestasi gagal disimpan.');
                }

                $storedPaths[$field] = $path;
            }

            $pengajuan = DB::transaction(function () use ($validated, $storedPaths, $mahasiswa): PengajuanPrestasi {
                return PengajuanPrestasi::create([
                    'mahasiswa_id' => $mahasiswa->getKey(),
                    'lomba' => $validated['lomba'],
                    'prestasi_hasil' => $validated['prestasi_hasil'],
                    'tanggal_pencapaian' => $validated['tanggal_pencapaian'],
                    'peserta_tim' => $validated['peserta_tim'],
                    'bukti_pendukung_path' => $storedPaths['bukti_pendukung'],
                    'keluaran_lomba_path' => $storedPaths['keluaran_lomba'],
                    'judul_arsip' => $validated['judul_arsip'],
                    'pengalaman_proses' => $validated['pengalaman_proses'],
                    'pembelajaran_saran' => $validated['pembelajaran_saran'],
                    'dokumen_pengalaman_path' => $storedPaths['dokumen_pengalaman'] ?? null,
                    'persetujuan_publikasi' => true,
                    'status' => PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI,
                ]);
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete(array_values($storedPaths));
            }

            throw $exception;
        }

        return redirect()
            ->route('mahasiswa.prestasi.create')
            ->with('pengajuan_baru', $pengajuan->getKey());
    }

    public function index(): View
    {
        $mahasiswa = $this->mahasiswa();
        $pengajuans = $mahasiswa->prestasiDiajukan()
            ->latest()
            ->latest('pengajuan_id')
            ->paginate(6)
            ->withQueryString();

        return view('mahasiswa.prestasi.index', compact('pengajuans'));
    }

    public function download(int $pengajuan, string $jenis): StreamedResponse
    {
        $mahasiswa = $this->mahasiswa();
        $record = $mahasiswa->prestasiDiajukan()->whereKey($pengajuan)->firstOrFail();

        $path = match ($jenis) {
            'bukti-pendukung' => $record->bukti_pendukung_path,
            'keluaran-lomba' => $record->keluaran_lomba_path,
            'dokumen-pengalaman' => $record->dokumen_pengalaman_path,
            default => null,
        };

        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $namaUnduhan = Str::slug($jenis).($extension === '' ? '' : '.'.$extension);

        return Storage::disk('local')->download($path, $namaUnduhan);
    }

    private function mahasiswa(): User
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->role === 'mahasiswa', 403);

        return $user;
    }
}
