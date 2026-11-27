<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\BasisPengetahuan;
use App\Models\PengajuanPrestasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengetahuanPrestasiController extends Controller
{
    public function index(Request $request): View
    {
        $this->mahasiswa();
        $search = trim($request->string('q')->toString());

        $articles = BasisPengetahuan::query()
            ->where('status', BasisPengetahuan::STATUS_DIPUBLIKASIKAN)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $matches) use ($search): void {
                    $matches->where('judul', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%")
                        ->orWhere('lomba', 'like', "%{$search}%")
                        ->orWhere('kontributor', 'like', "%{$search}%")
                        ->orWhere('ringkasan', 'like', "%{$search}%");
                });
            })
            ->latest('published_at')
            ->paginate(4, ['*'], 'arsip_page')
            ->withQueryString();

        $achievements = PengajuanPrestasi::query()
            ->with('mahasiswa:id,name')
            ->where('status', PengajuanPrestasi::STATUS_DISETUJUI)
            ->where('persetujuan_publikasi', true)
            ->where('publikasikan_prestasi', true)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $matches) use ($search): void {
                    $matches->where('prestasi_hasil', 'like', "%{$search}%")
                        ->orWhere('lomba', 'like', "%{$search}%")
                        ->orWhere('peserta_tim', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', fn (Builder $student) => $student
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('tanggal_pencapaian')
            ->paginate(4, ['*'], 'prestasi_page')
            ->withQueryString();

        return view(
            'mahasiswa.pengetahuan-prestasi.index',
            compact('articles', 'achievements', 'search')
        );
    }

    public function showArticle(
        Request $request,
        BasisPengetahuan $article
    ): View {
        $this->mahasiswa();

        abort_unless(
            $article->status === BasisPengetahuan::STATUS_DIPUBLIKASIKAN,
            404
        );

        return view('mahasiswa.pengetahuan-prestasi.article', compact('article'));
    }

    public function showAchievement(
        Request $request,
        PengajuanPrestasi $pengajuan
    ): View {
        $this->mahasiswa();

        abort_unless($this->achievementIsPublished($pengajuan), 404);
        $pengajuan->load('mahasiswa:id,name');

        return view(
            'mahasiswa.pengetahuan-prestasi.achievement',
            compact('pengajuan')
        );
    }

    public function downloadAchievementFile(
        Request $request,
        PengajuanPrestasi $pengajuan,
        string $jenis
    ): StreamedResponse {
        $this->mahasiswa();

        abort_unless($this->achievementIsPublished($pengajuan), 404);

        $path = match ($jenis) {
            'bukti' => $pengajuan->bukti_pendukung_path,
            'keluaran' => $pengajuan->keluaran_lomba_path,
            default => null,
        };

        abort_unless(
            is_string($path) && Storage::disk('local')->exists($path),
            404
        );

        return Storage::disk('local')->download(
            $path,
            $jenis.'.'.pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    private function achievementIsPublished(PengajuanPrestasi $pengajuan): bool
    {
        return $pengajuan->status === PengajuanPrestasi::STATUS_DISETUJUI
            && $pengajuan->persetujuan_publikasi
            && $pengajuan->publikasikan_prestasi;
    }

    private function mahasiswa(): User
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->role === 'mahasiswa', 403);

        return $user;
    }
}
