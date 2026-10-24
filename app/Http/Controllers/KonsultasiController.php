<?php

namespace App\Http\Controllers;

use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KonsultasiController extends Controller
{
    // Mahasiswa: daftar konsultasi miliknya
    public function index(Request $request): View
    {
        $this->pastikanRole($request, ['mahasiswa']);

        $items = Konsultasi::with('dosen')
            ->where('mahasiswa_id', $request->user()->id)
            ->latest()
            ->get();

        return view('konsultasi.index', compact('items'));
    }

    // Mahasiswa: form ajukan konsultasi
    public function create(Request $request): View
    {
        $this->pastikanRole($request, ['mahasiswa']);

        $dosenList = User::whereIn('role', ['dosen', 'kaprodi'])->orderBy('name')->get();

        return view('konsultasi.create', compact('dosenList'));
    }

    // Mahasiswa: simpan konsultasi
    public function store(Request $request): RedirectResponse
    {
        $this->pastikanRole($request, ['mahasiswa']);

        $data = $request->validate([
            'dosen_id' => ['required', Rule::exists('users', 'id')->whereIn('role', ['dosen', 'kaprodi'])],
            'pertanyaan' => ['required', 'string', 'max:1000'],
        ]);

        Konsultasi::create($data + [
            'mahasiswa_id' => $request->user()->id,
            'status' => 'menunggu',
        ]);

        return redirect()->route('konsultasi.index')
            ->with('status', 'Konsultasi terkirim. Tanggapan dosen akan muncul di halaman ini.');
    }

    // Dosen: daftar konsultasi yang ditujukan kepadanya
    public function dosenIndex(Request $request): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);

        $items = Konsultasi::with('mahasiswa')
            ->where('dosen_id', $request->user()->id)
            ->latest()
            ->get();

        return view('konsultasi.dosen-index', compact('items'));
    }

    private function pastikanRole(Request $request, array $roles): void
    {
        abort_unless($request->user() && in_array($request->user()->role, $roles, true), 403);
    }
}