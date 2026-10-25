<?php

namespace App\Http\Controllers\AdminHima;

use App\Http\Controllers\Controller;
use App\Models\Lomba;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LombaController extends Controller
{
    private const PENDAMPINGAN = ['tanpa pendampingan', 'konsultasi ringan', 'pembimbing resmi wajib'];
    private const STATUS       = ['dipublikasikan', 'diproses', 'draf', 'pending'];

    private function rules(): array
    {
        return [
            'judul_lomba'         => ['required', 'string', 'max:255'],
            'penyelenggara'       => ['required', 'string', 'max:255'],
            'kategori'            => ['required', 'string', 'max:255'],
            'tingkat'             => ['required', 'string', 'max:255'],
            'pendaftaran_dibuka'  => ['required', 'date'],
            'tenggat_pendaftaran' => ['required', 'date', 'after_or_equal:pendaftaran_dibuka'],
            'batas_pengumpulan'   => ['nullable', 'date'],
            'tanggal_final'       => ['nullable', 'date'],
            'jenis_pendampingan'  => ['required', Rule::in(self::PENDAMPINGAN)],
            'link_pendaftaran'    => ['required', 'url'],
            'deskripsi'           => ['required', 'string'],
            'status_publikasi'    => ['nullable', Rule::in(self::STATUS)],
        ];
    }

    // 1. Daftar lomba (dengan pencarian judul / penyelenggara)
    public function index(Request $request)
    {
        $lombas = Lomba::query()
            ->when($request->q, function ($query, $s) {
                $query->where(function ($w) use ($s) {
                    $w->where('judul_lomba', 'like', "%{$s}%")
                      ->orWhere('penyelenggara', 'like', "%{$s}%");
                });
            })
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('admin-hima.lomba.index', compact('lombas'));
    }

    // 2. Form tambah
    public function create()
    {
        return view('admin-hima.lomba.create');
    }

    // 3. Simpan lomba baru
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $data['status_publikasi'] ??= 'draf';
        $data['created_by'] = auth()->id();

        Lomba::create($data);

        return redirect()->route('admin.lomba.index')
            ->with('success', 'Data lomba berhasil ditambahkan!');
    }

    // 4. Form edit
    public function edit(Lomba $lomba)
    {
        return view('admin-hima.lomba.edit', compact('lomba'));
    }

    // 5. Update lomba
    public function update(Request $request, Lomba $lomba)
    {
        $data = $request->validate($this->rules());

        $data['status_publikasi'] ??= $lomba->status_publikasi;

        $lomba->update($data);

        return redirect()->route('admin.lomba.index')
            ->with('success', 'Data lomba berhasil diperbarui!');
    }

    // 6. Hapus lomba
    public function destroy(Lomba $lomba)
    {
        $lomba->delete();

        return redirect()->route('admin.lomba.index')
            ->with('success', 'Data lomba berhasil dihapus!');
    }
}