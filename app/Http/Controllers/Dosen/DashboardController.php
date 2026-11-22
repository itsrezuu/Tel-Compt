<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Konsultasi;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Pastikan pengguna adalah dosen atau kaprodi.
        $user = $request->user();

        abort_unless(
            $user && in_array($user->role, ['dosen', 'kaprodi'], true),
            403
        );

        // Perbarui pengajuan bimbingan yang sudah kedaluwarsa.
        Bimbingan::tolakKadaluarsa();

        $dosenId = $user->id;

        $awalBulan = now()->startOfMonth();
        $akhirBulan = now()->endOfMonth();

        // Query dasar berdasarkan dosen yang sedang login.
        $konsultasi = Konsultasi::where('dosen_id', $dosenId);

        $bimbingan = Bimbingan::where('dosen_id', $dosenId);

        // 1. Konsultasi yang masih menunggu tanggapan.
        $konsultasiMenunggu = (clone $konsultasi)
            ->where('status', Konsultasi::MENUNGGU)
            ->count();

        // 2. Bimbingan yang masih menunggu keputusan.
        $bimbinganMenunggu = (clone $bimbingan)
            ->where('status', Bimbingan::MENUNGGU)
            ->count();

        // 3. Konsultasi yang ditanggapi bulan ini.
        $konsultasiDitanggapi = (clone $konsultasi)
            ->where('status', Konsultasi::DITANGGAPI)
            ->whereBetween('responded_at', [$awalBulan, $akhirBulan])
            ->count();

        // 4. Bimbingan yang diproses bulan ini.
        $bimbinganDiproses = (clone $bimbingan)
            ->whereIn('status', [
                Bimbingan::DISETUJUI,
                Bimbingan::DITOLAK,
            ])
            ->whereBetween('responded_at', [$awalBulan, $akhirBulan])
            ->count();

        $bimbinganDisetujui = (clone $bimbingan)
            ->where('status', Bimbingan::DISETUJUI)
            ->whereBetween('responded_at', [$awalBulan, $akhirBulan])
            ->count();

        $bimbinganDitolak = (clone $bimbingan)
            ->where('status', Bimbingan::DITOLAK)
            ->whereBetween('responded_at', [$awalBulan, $akhirBulan])
            ->count();

        // 5. Menghitung rata-rata waktu respons bulan ini.
        $waktuKonsultasi = (clone $konsultasi)
            ->whereNotNull('responded_at')
            ->whereBetween('responded_at', [$awalBulan, $akhirBulan])
            ->get(['created_at', 'responded_at']);

        $waktuBimbingan = (clone $bimbingan)
            ->whereNotNull('responded_at')
            ->whereBetween('responded_at', [$awalBulan, $akhirBulan])
            ->get(['created_at', 'responded_at']);

        $seluruhRespons = $waktuKonsultasi->concat($waktuBimbingan);

        $rataRataDetik = $seluruhRespons->isNotEmpty()
            ? $seluruhRespons->avg(
                fn ($item) => $item->created_at->diffInSeconds(
                    $item->responded_at
                )
            )
            : null;

        $rataRataHari = $rataRataDetik !== null
            ? round($rataRataDetik / 86400, 1)
            : null;

        // 6. Mengambil tiga permintaan terbaru.
        $permintaanKonsultasi = (clone $konsultasi)
            ->with('mahasiswa')
            ->latest('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'kode' => $item->kode,
                    'judul' => 'Konsultasi · ' . $item->kode,
                    'nama' => $item->mahasiswa?->name ?? '-',
                    'tanggal' => $item->created_at,
                    'status' => $item->status,
                    'jenis' => 'Konsultasi',
                    'url' => route('dosen.konsultasi.show', $item->konsultasi_id),
                ];
            });

        $permintaanBimbingan = (clone $bimbingan)
            ->with('mahasiswa')
            ->latest('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'kode' => $item->kode,
                    'judul' => $item->topik_judul,
                    'nama' => $item->nama_tim,
                    'tanggal' => $item->created_at,
                    'status' => $item->status,
                    'jenis' => 'Bimbingan',
                    'url' => route('dosen.bimbingan.show', $item->bimbingan_id),
                ];
            });

        $permintaanTerbaru = $permintaanKonsultasi
            ->concat($permintaanBimbingan)
            ->sortByDesc('tanggal')
            ->take(3)
            ->values();

        // 7. Lomba yang paling banyak disimpan mahasiswa.
        // Bagian ini hanya dijalankan jika tabel bookmark tersedia.
        $lombaPopuler = collect();

        if (
            Schema::hasTable('lomba_bookmarks') &&
            Schema::hasTable('lomba') &&
            Schema::hasColumn('lomba_bookmarks', 'lomba_id')
        ) {
            $lombaPopuler = DB::table('lomba_bookmarks')
                ->join(
                    'lomba',
                    'lomba_bookmarks.lomba_id',
                    '=',
                    'lomba.lomba_id'
                )
                ->select(
                    'lomba.lomba_id',
                    'lomba.judul_lomba',
                    'lomba.kategori',
                    'lomba.tingkat',
                    'lomba.penyelenggara',
                    DB::raw('COUNT(*) as jumlah_simpan')
                )
                ->groupBy(
                    'lomba.lomba_id',
                    'lomba.judul_lomba',
                    'lomba.kategori',
                    'lomba.tingkat',
                    'lomba.penyelenggara'
                )
                ->orderByDesc('jumlah_simpan')
                ->limit(2)
                ->get();
        }

        return view('dosen.dashboard', compact(
            'user',
            'konsultasiMenunggu',
            'bimbinganMenunggu',
            'konsultasiDitanggapi',
            'bimbinganDiproses',
            'bimbinganDisetujui',
            'bimbinganDitolak',
            'rataRataHari',
            'permintaanTerbaru',
            'lombaPopuler'
        ));
    }
}
