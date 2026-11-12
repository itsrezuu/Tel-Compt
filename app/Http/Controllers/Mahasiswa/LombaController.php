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
     * Lomba yang disimpan disimpan di session (belum ada login).
     * Saat login sudah ada, ganti ke relasi User::lombaTersimpan()
     * (tabel lomba_bookmarks sudah tersedia).
     */
    private const SESSION_TERSIMPAN = 'lomba_tersimpan';

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

    public function show(Request $request, Lomba $lomba)
    {
        abort_unless($lomba->status_publikasi === Lomba::STATUS_TAYANG, 404);

        return view('mahasiswa.lomba.show', [
            'lomba'     => $lomba,
            'tersimpan' => in_array((int) $lomba->getKey(), $this->idTersimpan($request), true),
        ]);
    }

    public function simpan(Request $request, Lomba $lomba)
    {
        abort_unless($lomba->status_publikasi === Lomba::STATUS_TAYANG, 404);

        $id = $this->idTersimpan($request);

        $id = in_array((int) $lomba->getKey(), $id, true)
            ? array_values(array_diff($id, [(int) $lomba->getKey()]))
            : [...$id, (int) $lomba->getKey()];

        $request->session()->put(self::SESSION_TERSIMPAN, $id);

        return back();
    }

    /** @return int[] */
    private function idTersimpan(Request $request): array
    {
        return array_map('intval', $request->session()->get(self::SESSION_TERSIMPAN, []));
    }
}
