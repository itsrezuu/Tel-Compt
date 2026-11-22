<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Konsultasi;
use App\Models\PengajuanPrestasi;
use App\Models\UsulanLomba;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RiwayatAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = $request->user();

        abort_unless($mahasiswa && $mahasiswa->role === 'mahasiswa', 403);

        $aktivitas = collect();

        // 1. Riwayat bookmark lomba
        $bookmark = $mahasiswa->lombaTersimpan()->get();

        foreach ($bookmark as $lomba) {
            $aktivitas->push([
                'jenis' => 'Bookmark Lomba',
                'judul' => $lomba->judul_lomba,
                'deskripsi' => 'Kamu menyimpan lomba ini ke Lomba Saya.',
                'status' => 'Tersimpan',
                'tanggal' => $lomba->pivot->created_at,
                'url' => route('mahasiswa.lomba.show', $lomba),
            ]);
        }

        // 2. Riwayat usulan lomba
        $usulan = UsulanLomba::where('pengaju_id', $mahasiswa->id)
            ->latest()
            ->get();

        foreach ($usulan as $item) {
            $aktivitas->push([
                'jenis' => 'Usulan Lomba',
                'judul' => $item->judul_lomba,
                'deskripsi' => 'Usulan lomba yang kamu ajukan.',
                'status' => $item->status_label,
                'tanggal' => $item->created_at,
                'url' => route('mahasiswa.usulan.index'),
            ]);
        }

        // 3. Riwayat pengajuan prestasi
        $prestasi = $mahasiswa->prestasiDiajukan()
            ->latest()
            ->get();

        foreach ($prestasi as $item) {
            $aktivitas->push([
                'jenis' => 'Pengajuan Prestasi',
                'judul' => $item->lomba,
                'deskripsi' => 'Prestasi: ' . $item->prestasi_hasil,
                'status' => $item->status_label,
                'tanggal' => $item->created_at,
                'url' => route('mahasiswa.prestasi.index'),
            ]);
        }

        // 4. Riwayat bimbingan
        $bimbingan = Bimbingan::where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->get();

        foreach ($bimbingan as $item) {
            $aktivitas->push([
                'jenis' => 'Bimbingan',
                'judul' => $item->topik_judul,
                'deskripsi' => 'Permohonan bimbingan dengan dosen.',
                'status' => ucfirst(str_replace('_', ' ', $item->status)),
                'tanggal' => $item->created_at,
                'url' => route('bimbingan.show', $item),
            ]);
        }

        // 5. Riwayat konsultasi
        $konsultasi = Konsultasi::where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->get();

        foreach ($konsultasi as $item) {
            $aktivitas->push([
                'jenis' => 'Konsultasi',
                'judul' => 'Konsultasi Bimbingan',
                'deskripsi' => $item->pertanyaan,
                'status' => ucfirst(str_replace('_', ' ', $item->status)),
                'tanggal' => $item->created_at,
                'url' => route('bimbingan.show', $item->bimbingan_id),
            ]);
        }

        $aktivitas = $aktivitas
            ->sortByDesc('tanggal')
            ->values();

        return view('mahasiswa.riwayat.index', [
            'aktivitas' => $aktivitas,
        ]);
    }
}
