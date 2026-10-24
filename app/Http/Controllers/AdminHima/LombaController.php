<?php

namespace App\Http\Controllers\AdminHima;

use App\Http\Controllers\Controller;
use App\Models\Lomba;
use Illuminate\Http\Request;

class LombaController extends Controller
{
    // 1. Menampilkan daftar semua lomba untuk Admin HIMA
    public function index()
    {
        $lombas = Lomba::latest()->paginate(10);
        return view('admin-hima.lomba.index', compact('lombas'));
    }

    // 2. Menampilkan form tambah lomba
    public function create()
    {
        return view('admin-hima.lomba.create');
    }

    // 3. Menyimpan data lomba baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'judul_lomba'         => 'required|string|max:255',
            'penyelenggara'       => 'required|string|max:255',
            'kategori'            => 'required|string|max:255',
            'tingkat'             => 'required|string|max:255',
            'tenggat_pendaftaran' => 'required|date',
            'link_pendaftaran'    => 'required|url',
            'jenis_pendampingan'  => 'required|in:tanpa pendampingan,konsultasi ringan,pembimbing resmi wajib',
        ]);

        Lomba::create([
            'judul_lomba'         => $request->judul_lomba,
            'penyelenggara'       => $request->penyelenggara,
            'kategori'            => $request->kategori,
            'tingkat'             => $request->tingkat,
            'tenggat_pendaftaran' => $request->tenggat_pendaftaran,
            'link_pendaftaran'    => $request->link_pendaftaran,
            'jenis_pendampingan'  => $request->jenis_pendampingan,
            'status_publikasi'    => 'pending', // Default status sesuai alur admin
            'created_by'          => auth()->id(), // Mencatat id admin yang menginput
        ]);

        return redirect()->route('admin.lomba.index')->with('success', 'Data lomba berhasil ditambahkan!');
    }

    // 4. Menampilkan form edit lomba
    public function edit(Lomba $lomba)
    {
        return view('admin-hima.lomba.edit', compact('lomba'));
    }

    // 5. Mengupdate data lomba yang sudah ada
    public function update(Request $request, Lomba $lomba)
    {
        $request->validate([
            'judul_lomba'         => 'required|string|max:255',
            'penyelenggara'       => 'required|string|max:255',
            'kategori'            => 'required|string|max:255',
            'tingkat'             => 'required|string|max:255',
            'tenggat_pendaftaran' => 'required|date',
            'link_pendaftaran'    => 'required|url',
            'jenis_pendampingan'  => 'required|in:tanpa pendampingan,konsultasi ringan,pembimbing resmi wajib',
            'status_publikasi'    => 'required|string',
        ]);

        $lomba->update($request->all());

        return redirect()->route('admin.lomba.index')->with('success', 'Data lomba berhasil diperbarui!');
    }

    // 6. Menghapus data lomba
    public function destroy(Lomba $lomba)
    {
        $lomba->delete();

        return redirect()->route('admin.lomba.index')->with('success', 'Data lomba berhasil dihapus!');
    }
}