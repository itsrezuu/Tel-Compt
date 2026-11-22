<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\BidangKeahlian;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfilController extends Controller
{
    /**
     * Menampilkan profil mahasiswa.
     */
    public function index()
    {
        $mahasiswa = auth()->user();

        abort_unless($mahasiswa && $mahasiswa->role === 'mahasiswa', 403);

        $daftarKampus = ProgramStudi::query()
            ->select('kampus')
            ->distinct()
            ->orderBy('kampus')
            ->pluck('kampus');

        $daftarProgramStudi = ProgramStudi::query()
            ->orderBy('nama_prodi')
            ->get();

        return view('mahasiswa.profil.index', [
            'mahasiswa' => $mahasiswa,
            'bidangKeahlian' => BidangKeahlian::orderBy('nama_bidang')->get(),
            'selectedBidang' => $mahasiswa->minatBidang()
                ->pluck('bidang_keahlian.bidang_keahlian_id')
                ->toArray(),
            'daftarKampus' => $daftarKampus,
            'daftarProgramStudi' => $daftarProgramStudi,
        ]);
    }

    /**
     * Memperbarui deskripsi dan bidang minat mahasiswa.
     */

    public function update(Request $request)
    {
        $mahasiswa = $request->user();

        abort_unless($mahasiswa && $mahasiswa->role === 'mahasiswa', 403);

        $validated = $request->validate([
            'program_studi_id' => [
                'nullable',
                'integer',
                'exists:program_studi,id',
            ],
            'deskripsi_keahlian' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'bidang_keahlian' => [
                'nullable',
                'array',
            ],
            'bidang_keahlian.*' => [
                'required',
                'integer',
                'distinct',
                'exists:bidang_keahlian,bidang_keahlian_id',
            ],
        ], [
            'program_studi_id.exists' => 'Program studi yang dipilih tidak valid.',
            'bidang_keahlian.*.exists' => 'Pilihan bidang minat tidak valid.',
            'bidang_keahlian.*.distinct' => 'Bidang minat tidak boleh dipilih dua kali.',
        ]);

        DB::transaction(function () use ($mahasiswa, $validated) {
            $mahasiswa->program_studi_id = $validated['program_studi_id'] ?? null;

            $mahasiswa->update([
                'deskripsi_keahlian' => $validated['deskripsi_keahlian'] ?? null,
            ]);

            $mahasiswa->save();

            $mahasiswa->minatBidang()->sync(
                $validated['bidang_keahlian'] ?? []
            );
        });

        return redirect()
            ->route('mahasiswa.profil.index')
            ->with('success', 'Profil dan bidang minat berhasil diperbarui.');
    }
}
