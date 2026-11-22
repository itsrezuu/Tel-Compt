<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Lomba;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LombaController extends Controller
{
    private const PER_HALAMAN = 6;

    /**
     * Menampilkan katalog lomba.
     */
    public function index(Request $request)
    {
        $filter = $request->validate([
            'q'            => ['nullable', 'string', 'max:100'],
            'kategori'     => ['nullable', Rule::in(Lomba::KATEGORI)],
            'tingkat'      => ['nullable', Rule::in(Lomba::TINGKAT)],
            'pendampingan' => ['nullable', Rule::in(array_keys(Lomba::PENDAMPINGAN))],
            'urut'         => ['nullable', Rule::in(['tenggat', 'terbaru'])],
        ]);

        $daftarLomba = Lomba::query()
            ->tayang()
            ->masihBuka()
            ->cari($filter['q'] ?? null)
            ->saring($filter)
            ->urutkan($filter['urut'] ?? 'tenggat')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('mahasiswa.lomba.index', [
            'daftarLomba'        => $daftarLomba,
            'filter'             => $filter,
            'idTersimpan'        => $this->idTersimpan($request),
            'daftarKategori'     => Lomba::KATEGORI,
            'daftarTingkat'      => Lomba::TINGKAT,
            'daftarPendampingan' => Lomba::PENDAMPINGAN,
        ]);
    }

    /**
     * Menampilkan detail lomba.
     */
    public function show(Request $request, Lomba $lomba)
    {
        abort_unless($lomba->status_publikasi === Lomba::STATUS_TAYANG, 404);

        return view('mahasiswa.lomba.show', [
            'lomba' => $lomba,
            'tersimpan' => $request->user()
                ->lombaTersimpan()
                ->where('lomba.lomba_id', $lomba->getKey())
                ->exists(),
        ]);
    }

    /**
     * Menyimpan atau menghapus bookmark lomba.
     */
    public function simpan(Request $request, Lomba $lomba)
    {
        abort_unless($lomba->status_publikasi === Lomba::STATUS_TAYANG, 404);

        $bookmark = $request->user()
            ->lombaTersimpan()
            ->where('lomba.lomba_id', $lomba->getKey())
            ->exists();

        if ($bookmark) {
            $request->user()->lombaTersimpan()->detach($lomba->getKey());

            return back()->with('success', 'Lomba dihapus dari Lomba Saya.');
        }

        $request->user()->lombaTersimpan()->attach($lomba->getKey());

        return back()->with('success', 'Lomba berhasil disimpan ke Lomba Saya.');
    }

    /**
     * Mengambil ID lomba yang sudah disimpan mahasiswa.
     *
     * @return int[]
     */
    private function idTersimpan(Request $request): array
    {
        return $request->user()
            ->lombaTersimpan()
            ->pluck('lomba.lomba_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
