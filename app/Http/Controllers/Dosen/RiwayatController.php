<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Konsultasi;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    public function index(Request $request): View
    {
        $this->pastikanRole($request, ['dosen', 'kaprodi']);

        $jenis = in_array($request->query('jenis'), ['semua', 'konsultasi', 'bimbingan'], true)
            ? (string) $request->query('jenis')
            : 'semua';

        $periode = $request->query('periode');
        $periode = is_string($periode) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D', $periode) === 1
            ? $periode
            : '';

        $filtered = DB::query()
            ->fromSub($this->permintaanSelesai((int) $request->user()->getKey()), 'riwayat')
            ->select('riwayat.*')
            ->when($jenis !== 'semua', fn (Builder $query): Builder => $query->where('jenis', $jenis));

        if ($periode !== '') {
            $bulan = Carbon::parse($periode.'-01');
            $filtered->whereBetween('selesai', [
                $bulan->copy()->startOfMonth(),
                $bulan->copy()->endOfMonth(),
            ]);
        }

        $items = (clone $filtered)
            ->orderByDesc('selesai')
            ->orderByDesc('id')
            ->orderBy('jenis')
            ->paginate(6)
            ->withQueryString();

        $items->setCollection($items->getCollection()->map(
            fn (object $item): object => $this->withCode($item),
        ));

        $pilih = null;
        $pilihan = $request->query('pilih');

        if (is_string($pilihan) && preg_match('/^(konsultasi|bimbingan):([1-9]\d*)$/D', $pilihan, $parts) === 1) {
            $pilih = (clone $filtered)
                ->where('jenis', $parts[1])
                ->where('id', (int) $parts[2])
                ->first();
        }

        $pilih = $pilih ? $this->withCode($pilih) : $items->getCollection()->first();

        return view('dosen.riwayat', compact('items', 'jenis', 'periode', 'pilih'));
    }

    private function permintaanSelesai(int $dosenId): Builder
    {
        $bimbingan = DB::table('bimbingan as b')
            ->join('users as m', 'm.id', '=', 'b.mahasiswa_id')
            ->selectRaw("'bimbingan' as jenis, b.bimbingan_id as id, b.topik_judul as permintaan, m.name as mahasiswa, b.nama_tim as nama_tim, CASE WHEN b.status = ? THEN b.updated_at ELSE b.responded_at END as selesai, b.status as status, b.catatan_dosen as catatan", [Bimbingan::SELESAI])
            ->where('b.dosen_id', $dosenId)
            ->whereIn('b.status', [Bimbingan::DISETUJUI, Bimbingan::DITOLAK, Bimbingan::SELESAI])
            ->whereNotNull('b.responded_at')
            ->whereNotNull('b.catatan_dosen')
            ->where('b.catatan_dosen', '<>', '');

        $konsultasi = DB::table('konsultasi as k')
            ->join('bimbingan as b', 'b.bimbingan_id', '=', 'k.bimbingan_id')
            ->join('users as m', 'm.id', '=', 'k.mahasiswa_id')
            ->selectRaw("'konsultasi' as jenis, k.konsultasi_id as id, k.pertanyaan as permintaan, m.name as mahasiswa, b.nama_tim as nama_tim, k.responded_at as selesai, k.status as status, k.jawaban_arahan as catatan")
            ->where('k.dosen_id', $dosenId)
            ->where('b.dosen_id', $dosenId)
            ->whereIn('k.status', [Konsultasi::DITANGGAPI, Konsultasi::DITOLAK])
            ->whereNotNull('k.responded_at')
            ->whereNotNull('k.jawaban_arahan')
            ->where('k.jawaban_arahan', '<>', '');

        return $bimbingan->unionAll($konsultasi);
    }

    private function withCode(object $item): object
    {
        $prefix = $item->jenis === 'bimbingan' ? 'B' : 'K';
        $item->kode = $prefix.'-'.str_pad((string) $item->id, 3, '0', STR_PAD_LEFT);

        return $item;
    }

    private function pastikanRole(Request $request, array $roles): void
    {
        abort_unless($request->user() && in_array($request->user()->role, $roles, true), 403);
    }
}
