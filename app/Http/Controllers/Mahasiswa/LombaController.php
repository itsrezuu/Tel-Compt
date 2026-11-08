<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Lomba;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LombaController extends Controller
{
    private const PER_HALAMAN = 6;

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

        $idTersimpan = $request->user()
            ->lombaTersimpan()
            ->whereIn('lomba.id', $daftarLomba->pluck('id'))
            ->pluck('lomba.id')
            ->all();

        return view('mahasiswa.lomba.index', [
            'daftarLomba'        => $daftarLomba,
            'filter'             => $filter,
            'idTersimpan'        => $idTersimpan,
            'daftarKategori'     => Lomba::KATEGORI,
            'daftarTingkat'      => Lomba::TINGKAT,
            'daftarPendampingan' => Lomba::PENDAMPINGAN,
        ]);
    }

    public function show(Request $request, Lomba $lomba)
    {
        abort_unless($lomba->status === Lomba::STATUS_TAYANG, 404);

        return view('mahasiswa.lomba.show', [
            'lomba'     => $lomba,
            'tersimpan' => $request->user()->lombaTersimpan()
                ->where('lomba.id', $lomba->id)->exists(),
        ]);
    }

    public function simpan(Request $request, Lomba $lomba)
    {
        abort_unless($lomba->status === Lomba::STATUS_TAYANG, 404);

        $request->user()->lombaTersimpan()->toggle($lomba->id);

        return back();
    }
}
