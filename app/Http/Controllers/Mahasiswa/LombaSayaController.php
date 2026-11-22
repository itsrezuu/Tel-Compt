<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LombaSayaController extends Controller
{
    /**
     * Menampilkan daftar lomba yang disimpan mahasiswa.
     */
    public function index(Request $request)
    {
        $mahasiswa = $request->user();

        abort_unless($mahasiswa && $mahasiswa->role === 'mahasiswa', 403);

        $daftarLomba = $mahasiswa->lombaTersimpan()
            ->orderByPivot('created_at', 'desc')
            ->paginate(6);

        $idTersimpan = $daftarLomba->getCollection()
            ->map(fn ($lomba) => (int) $lomba->getKey())
            ->all();

        return view('mahasiswa.lomba-saya.index', [
            'daftarLomba' => $daftarLomba,
            'idTersimpan' => $idTersimpan,
        ]);
    }
}
