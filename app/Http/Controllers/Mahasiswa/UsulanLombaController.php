<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UsulanLomba;
use App\Notifications\ActivityNotification;
use Illuminate\Http\Request;

class UsulanLombaController extends Controller
{
    // Form usulan + keadaan usulan terakhir
    public function create()
    {
        if (session('usulan_baru')) {
            $terakhir = UsulanLomba::find(session('usulan_baru'));
        } elseif (auth()->check()) {
            $terakhir = UsulanLomba::where('pengaju_id', auth()->id())->latest()->first();
        } else {
            $terakhir = null;
        }

        return view('mahasiswa.usulan.create', compact('terakhir'));
    }

    // Simpan usulan baru (status awal: menunggu)
    public function store(Request $request)
    {
        $data = $request->validate([
            'judul_lomba' => ['required', 'string', 'max:255'],
            'link_resmi' => ['required', 'url', 'max:2048'],
            'deskripsi' => ['required', 'string', 'max:5000'],
        ], [
            'judul_lomba.required' => 'Nama lomba wajib diisi.',
            'link_resmi.required' => 'Tautan resmi wajib diisi.',
            'link_resmi.url' => 'Tautan harus diawali https:// atau http://.',
            'deskripsi.required' => 'Deskripsi lomba wajib diisi.',
        ]);

        $usulan = UsulanLomba::create($data + [
            'pengaju_id' => auth()->id(), // null jika belum ada login
            'status' => 'menunggu',
        ]);

        foreach (User::query()->where('role', 'admin')->get() as $admin) {
            $admin->notify(new ActivityNotification(
                'Pengajuan lomba baru',
                "{$usulan->judul_lomba} menunggu ditinjau.",
                'usulan_dibuat',
                (int) $usulan->usulan_id,
                route('admin.usulan.show', $usulan),
            ));
        }

        return redirect()->route('mahasiswa.usulan.create')->with('usulan_baru', $usulan->usulan_id);
    }

    // Riwayat usulan milik mahasiswa
    public function index()
    {
        $usulans = UsulanLomba::query()
            ->when(
                auth()->check(),
                fn ($q) => $q->where('pengaju_id', auth()->id()),
                fn ($q) => $q->whereNull('pengaju_id')
            )
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('mahasiswa.usulan.index', compact('usulans'));
    }
}
