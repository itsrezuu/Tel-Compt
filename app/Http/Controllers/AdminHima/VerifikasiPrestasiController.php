<?php

namespace App\Http\Controllers\AdminHima;

use App\Http\Controllers\Controller;
use App\Models\PengajuanPrestasi;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerifikasiPrestasiController extends Controller
{
    public function index(Request $request): View
    {
        $this->admin($request);
        $tab = (string) $request->query('status', PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI);
        $tabs = [
            PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI,
            'arsip_menunggu',
            PengajuanPrestasi::STATUS_DISETUJUI,
            PengajuanPrestasi::STATUS_DITOLAK,
        ];
        if (! in_array($tab, $tabs, true)) {
            $tab = PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI;
        }

        $counts = [
            PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI => PengajuanPrestasi::where('status', PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI)->count(),
            'arsip_menunggu' => PengajuanPrestasi::where('status', PengajuanPrestasi::STATUS_DISETUJUI)
                ->where('status_arsip', PengajuanPrestasi::STATUS_ARSIP_MENUNGGU)->count(),
        ];
        $pengajuans = PengajuanPrestasi::with('mahasiswa:id,name')
            ->when($tab === PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI, fn (Builder $query) => $query->where('status', PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI))
            ->when($tab === 'arsip_menunggu', fn (Builder $query) => $query->where('status', PengajuanPrestasi::STATUS_DISETUJUI)
                ->where('status_arsip', PengajuanPrestasi::STATUS_ARSIP_MENUNGGU))
            ->when($tab === PengajuanPrestasi::STATUS_DISETUJUI, fn (Builder $query) => $query->where('status', PengajuanPrestasi::STATUS_DISETUJUI)
                ->where('status_arsip', '!=', PengajuanPrestasi::STATUS_ARSIP_MENUNGGU))
            ->when($tab === PengajuanPrestasi::STATUS_DITOLAK, fn (Builder $query) => $query->where('status', PengajuanPrestasi::STATUS_DITOLAK))
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $search = trim($request->string('q')->toString());
                $query->where(function (Builder $matches) use ($search): void {
                    $matches->where('lomba', 'like', "%{$search}%")
                        ->orWhere('peserta_tim', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', fn (Builder $student) => $student->where('name', 'like', "%{$search}%"));
                    if (preg_match('/^P?-?0*(\d+)$/i', $search, $number) === 1) {
                        $matches->orWhere('pengajuan_id', (int) $number[1]);
                    }
                });
            })
            ->latest('pengajuan_id')
            ->paginate(3)
            ->withQueryString();

        return view('admin-hima.verifikasi.index', compact('pengajuans', 'tab', 'counts'));
    }

    public function show(Request $request, PengajuanPrestasi $pengajuan): View
    {
        $this->admin($request);
        $pengajuan->load('mahasiswa:id,name');

        return view('admin-hima.verifikasi.show', compact('pengajuan'));
    }

    public function download(Request $request, PengajuanPrestasi $pengajuan, string $jenis): StreamedResponse
    {
        $this->admin($request);
        $path = match ($jenis) {
            'bukti-pendukung' => $pengajuan->bukti_pendukung_path,
            'keluaran-lomba' => $pengajuan->keluaran_lomba_path,
            'dokumen-pengalaman' => $pengajuan->dokumen_pengalaman_path,
            default => null,
        };
        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, Str::slug($jenis).'.'.pathinfo($path, PATHINFO_EXTENSION));
    }

    public function setujui(Request $request, PengajuanPrestasi $pengajuan): RedirectResponse
    {
        $this->admin($request);
        abort_unless($pengajuan->status === PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI
            || ($pengajuan->status === PengajuanPrestasi::STATUS_DISETUJUI && $pengajuan->status_arsip === PengajuanPrestasi::STATUS_ARSIP_MENUNGGU), 404);

        $data = $request->validate([
            'bukti_sesuai' => ['required', 'accepted'],
            'keluaran_lomba_tersedia' => ['required', 'accepted'],
            'pengalaman_layak' => ['nullable', 'boolean'],
            'catatan_keputusan' => ['nullable', 'string', 'max:2000'],
            'publikasikan_prestasi' => ['sometimes', 'boolean'],
            'publikasikan_arsip' => ['sometimes', 'boolean'],
        ], [
            'bukti_sesuai.accepted' => 'Bukti harus dinyatakan sesuai sebelum prestasi disetujui.',
            'keluaran_lomba_tersedia.accepted' => 'Keluaran lomba harus tersedia sebelum prestasi disetujui.',
        ]);

        $statusAwal = $pengajuan->status;
        $arsipLayak = $request->boolean('pengalaman_layak');
        $bolehPublikasi = (bool) $pengajuan->persetujuan_publikasi;
        DB::transaction(function () use ($pengajuan, $data, $statusAwal, $arsipLayak, $bolehPublikasi, $request): void {
            $attributes = [
                'bukti_sesuai' => true,
                'keluaran_lomba_tersedia' => true,
                'pengalaman_layak' => $arsipLayak,
                'catatan_keputusan' => $data['catatan_keputusan'] ?? null,
                'publikasikan_arsip' => $bolehPublikasi && $arsipLayak && $request->boolean('publikasikan_arsip'),
                'ditinjau_oleh' => $request->user()->getKey(),
                'ditinjau_pada' => now(),
            ];
            if ($statusAwal === PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI) {
                $attributes['publikasikan_prestasi'] = $bolehPublikasi && $request->boolean('publikasikan_prestasi');
                $attributes['status'] = PengajuanPrestasi::STATUS_DISETUJUI;
                $attributes['status_arsip'] = $arsipLayak
                    ? PengajuanPrestasi::STATUS_ARSIP_DISETUJUI
                    : PengajuanPrestasi::STATUS_ARSIP_MENUNGGU;
            } else {
                abort_unless($arsipLayak, 422, 'Arsip harus dinyatakan layak sebelum disetujui.');
                $attributes['status_arsip'] = PengajuanPrestasi::STATUS_ARSIP_DISETUJUI;
            }
            $pengajuan->update($attributes);
        });

        $pengajuan->refresh();
        $pengajuan->mahasiswa?->notify(new ActivityNotification(
            $statusAwal === PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI ? 'Prestasi disetujui' : 'Arsip pengalaman disetujui',
            $statusAwal === PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI
                ? "Prestasi {$pengajuan->lomba} disetujui. Catatan: ".($pengajuan->catatan_keputusan ?: 'Tidak ada catatan tambahan.')
                : "Arsip pengalaman {$pengajuan->judul_arsip} disetujui untuk moderasi.",
            'prestasi_status',
            (int) $pengajuan->getKey(),
            route('mahasiswa.prestasi.index'),
        ));

        return redirect()->route('admin.verifikasi.show', $pengajuan)->with('success', 'Keputusan berhasil disimpan.');
    }

    public function tolak(Request $request, PengajuanPrestasi $pengajuan): RedirectResponse
    {
        $this->admin($request);
        abort_unless($pengajuan->status === PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI
            || ($pengajuan->status === PengajuanPrestasi::STATUS_DISETUJUI && $pengajuan->status_arsip === PengajuanPrestasi::STATUS_ARSIP_MENUNGGU), 404);
        $data = $request->validate(['catatan_keputusan' => ['required', 'string', 'max:2000']], [
            'catatan_keputusan.required' => 'Catatan keputusan wajib diisi saat menolak pengajuan.',
        ]);

        $penolakanPrestasi = $pengajuan->status === PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI;
        $attributes = [
            'status' => $penolakanPrestasi ? PengajuanPrestasi::STATUS_DITOLAK : PengajuanPrestasi::STATUS_DISETUJUI,
            'status_arsip' => PengajuanPrestasi::STATUS_ARSIP_DITOLAK,
            'catatan_keputusan' => $data['catatan_keputusan'],
            'publikasikan_arsip' => false,
            'ditinjau_oleh' => $request->user()->getKey(),
            'ditinjau_pada' => now(),
        ];
        if ($penolakanPrestasi) {
            $attributes['publikasikan_prestasi'] = false;
        }
        $pengajuan->update($attributes);

        $pengajuan->mahasiswa?->notify(new ActivityNotification(
            $penolakanPrestasi ? 'Pengajuan prestasi ditolak' : 'Arsip pengalaman ditolak',
            "{$pengajuan->nomor_pengajuan} ditolak. Alasan: {$data['catatan_keputusan']}",
            'prestasi_status',
            (int) $pengajuan->getKey(),
            route('mahasiswa.prestasi.index'),
        ));

        return redirect()->route('admin.verifikasi.index', ['status' => $penolakanPrestasi ? 'ditolak' : 'arsip_menunggu'])
            ->with('success', 'Keputusan penolakan berhasil disimpan.');
    }

    private function admin(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->role === 'admin', 403);

        return $user;
    }
}
