<?php

namespace App\Http\Controllers\AdminHima;

use App\Http\Controllers\Controller;
use App\Models\BasisPengetahuan;
use App\Models\PengajuanPrestasi;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KontenController extends Controller
{
    public function index(Request $request): View
    {
        $this->admin($request);
        $jenis = $request->query('jenis', 'semua');
        $status = $request->query('status', 'semua');
        $search = trim($request->string('q')->toString());

        if (! in_array($jenis, ['semua', 'basis_pengetahuan', 'galeri_prestasi'], true)) {
            $jenis = 'semua';
        }

        if (! in_array($status, ['semua', 'dipublikasikan', 'draf'], true)) {
            $status = 'semua';
        }

        $items = null;

        if ($jenis !== 'galeri_prestasi') {
            $items = DB::table('basis_pengetahuan')->select([
                DB::raw("'basis_pengetahuan' as jenis"),
                'id',
                'judul',
                'lomba as subjudul',
                'kontributor as sumber',
                'status',
                'updated_at',
            ]);
        }

        if ($jenis !== 'basis_pengetahuan') {
            $achievements = DB::table('pengajuan_prestasi')
                ->join('users', 'users.id', '=', 'pengajuan_prestasi.mahasiswa_id')
                ->where('pengajuan_prestasi.status', PengajuanPrestasi::STATUS_DISETUJUI)
                ->where('pengajuan_prestasi.persetujuan_publikasi', true)
                ->select([
                    DB::raw("'galeri_prestasi' as jenis"),
                    'pengajuan_prestasi.pengajuan_id as id',
                    'pengajuan_prestasi.prestasi_hasil as judul',
                    'pengajuan_prestasi.lomba as subjudul',
                    'users.name as sumber',
                    DB::raw("
                        CASE
                            WHEN pengajuan_prestasi.publikasikan_prestasi = 1
                            THEN 'dipublikasikan'
                            ELSE 'draf'
                        END as status
                    "),
                    'pengajuan_prestasi.updated_at',
                ]);

            $items = $items
                ? $items->unionAll($achievements)
                : $achievements;
        }

        $content = DB::query()
            ->fromSub($items, 'konten')
            ->when($status !== 'semua', fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $matches) use ($search): void {
                    $matches->where('judul', 'like', "%{$search}%")
                        ->orWhere('subjudul', 'like', "%{$search}%")
                        ->orWhere('sumber', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(8)
            ->withQueryString();

        $jumlahPrestasiLayakTayang = PengajuanPrestasi::query()
            ->where('status', PengajuanPrestasi::STATUS_DISETUJUI)
            ->where('persetujuan_publikasi', true);

        $counts = [
            'semua' => BasisPengetahuan::count() + (clone $jumlahPrestasiLayakTayang)->count(),
            'basis_pengetahuan' => BasisPengetahuan::count(),
            'galeri_prestasi' => (clone $jumlahPrestasiLayakTayang)->count(),
            'draf' => BasisPengetahuan::where('status', BasisPengetahuan::STATUS_DRAF)->count()
                + (clone $jumlahPrestasiLayakTayang)
                    ->where('publikasikan_prestasi', false)
                    ->count(),
        ];

        return view('admin-hima.konten.index', compact(
            'content',
            'jenis',
            'status',
            'counts'
        ));
    }

    public function create(Request $request): View
    {
        $this->admin($request);

        return view('admin-hima.konten.form', [
            'article' => new BasisPengetahuan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $admin = $this->admin($request);
        $data = $request->validate($this->rules());

        $data['created_by'] = $admin->getKey();
        $data['published_at'] = $data['status'] === BasisPengetahuan::STATUS_DIPUBLIKASIKAN
            ? now()
            : null;

        BasisPengetahuan::create($data);

        return redirect()
            ->route('admin.konten.index')
            ->with('success', 'Konten Basis Pengetahuan berhasil ditambahkan.');
    }

    public function edit(Request $request, BasisPengetahuan $article): View
    {
        $this->admin($request);

        return view('admin-hima.konten.form', compact('article'));
    }

    public function update(Request $request, BasisPengetahuan $article): RedirectResponse
    {
        $this->admin($request);
        $data = $request->validate($this->rules());

        $data['published_at'] = $data['status'] === BasisPengetahuan::STATUS_DIPUBLIKASIKAN
            ? ($article->published_at ?? now())
            : null;

        $article->update($data);

        return redirect()
            ->route('admin.konten.index')
            ->with('success', 'Konten berhasil diperbarui.');
    }

    public function toggleArticlePublication(
        Request $request,
        BasisPengetahuan $article
    ): RedirectResponse {
        $this->admin($request);

        $published = $article->status !== BasisPengetahuan::STATUS_DIPUBLIKASIKAN;

        $article->update([
            'status' => $published
                ? BasisPengetahuan::STATUS_DIPUBLIKASIKAN
                : BasisPengetahuan::STATUS_DRAF,
            'published_at' => $published ? now() : null,
        ]);

        return back()->with(
            'success',
            $published ? 'Konten telah dipublikasikan.' : 'Publikasi konten telah ditarik.'
        );
    }

    public function toggleAchievementPublication(
        Request $request,
        PengajuanPrestasi $pengajuan
    ): RedirectResponse {
        $this->admin($request);

        abort_unless(
            $pengajuan->status === PengajuanPrestasi::STATUS_DISETUJUI
                && $pengajuan->persetujuan_publikasi,
            404
        );

        $pengajuan->update([
            'publikasikan_prestasi' => ! $pengajuan->publikasikan_prestasi,
        ]);

        return back()->with(
            'success',
            $pengajuan->publikasikan_prestasi
                ? 'Prestasi ditampilkan di Galeri Prestasi.'
                : 'Publikasi prestasi telah ditarik.'
        );
    }

    /** @return array<string, array<int, string>> */
    private function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'lomba' => ['nullable', 'string', 'max:255'],
            'kontributor' => ['nullable', 'string', 'max:255'],
            'ringkasan' => ['required', 'string', 'max:500'],
            'isi' => ['required', 'string', 'max:20000'],
            'status' => ['required', 'in:draf,dipublikasikan'],
        ];
    }

    private function admin(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User && $user->role === 'admin', 403);

        return $user;
    }
}
