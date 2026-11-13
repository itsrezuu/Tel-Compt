<?php

namespace App\Http\Controllers\AdminHima;

use App\Http\Controllers\Controller;
use App\Models\Lomba;
use App\Models\UsulanLomba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsulanController extends Controller
{
    private const PENDAMPINGAN = ['tanpa pendampingan', 'konsultasi ringan', 'pembimbing resmi wajib'];

    // Aturan data lomba yang dilengkapi admin saat publikasi (sama dengan LombaController)
    private function lombaRules(): array
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
        ];
    }

    // Antrean usulan (tab status + pencarian)
    public function index(Request $request)
    {
        $status = $request->query('status', 'menunggu');
        if (! in_array($status, UsulanLomba::STATUSES, true)) {
            $status = 'menunggu';
        }

        $counts = UsulanLomba::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $usulans = UsulanLomba::with('pengaju')
            ->where('status', $status)
            ->when($request->q, function ($query, $s) {
                $s = trim($s);
                $query->where(function ($w) use ($s) {
                    $w->where('judul_lomba', 'like', "%{$s}%")
                      ->orWhereHas('pengaju', fn ($p) => $p->where('name', 'like', "%{$s}%"));

                    // Cari berdasarkan nomor usulan, mis. "U-026" atau "26"
                    if (preg_match('/^U?-?0*(\d+)$/i', $s, $m)) {
                        $w->orWhere('usulan_id', (int) $m[1]);
                    }
                });
            })
            ->oldest()
            ->paginate(4)
            ->withQueryString();

        return view('admin-hima.usulan.index', compact('usulans', 'status', 'counts'));
    }

    // Halaman tinjauan satu usulan
    public function show(UsulanLomba $usulan)
    {
        $usulan->load(['pengaju', 'lomba']);

        return view('admin-hima.usulan.show', compact('usulan'));
    }

    // menunggu -> diproses
    public function proses(UsulanLomba $usulan)
    {
        if ($usulan->status !== 'menunggu') {
            return back()->with('error', 'Hanya usulan berstatus Menunggu yang bisa diproses.');
        }

        $usulan->update([
            'status'        => 'diproses',
            'diproses_oleh' => auth()->id(),
        ]);

        return redirect()->route('admin.usulan.show', $usulan)
            ->with('success', 'Usulan mulai diproses.');
    }

    // menunggu/diproses -> dipublikasikan (sekaligus membuat baris di tabel lomba)
    public function publikasikan(Request $request, UsulanLomba $usulan)
    {
        if (! in_array($usulan->status, ['menunggu', 'diproses'], true)) {
            return back()->with('error', 'Usulan ini sudah selesai diproses.');
        }

        $data = $request->validate($this->lombaRules());
        $data['status_publikasi'] = 'dipublikasikan';
        $data['created_by'] = auth()->id();

        DB::transaction(function () use ($data, $usulan) {
            $lomba = Lomba::create($data);

            $usulan->update([
                'status'        => 'dipublikasikan',
                'lomba_id'      => $lomba->lomba_id,
                'diproses_oleh' => auth()->id(),
            ]);
        });

        return redirect()->route('admin.usulan.index', ['status' => 'dipublikasikan'])
            ->with('success', 'Usulan berhasil dipublikasikan sebagai lomba.');
    }

    // menunggu/diproses -> ditolak (alasan wajib)
    public function tolak(Request $request, UsulanLomba $usulan)
    {
        if (! in_array($usulan->status, ['menunggu', 'diproses'], true)) {
            return back()->with('error', 'Usulan ini sudah selesai diproses.');
        }

        $data = $request->validateWithBag('tolak', [
            'catatan_admin' => ['required', 'string', 'max:1000'],
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $usulan->update([
            'status'        => 'ditolak',
            'catatan_admin' => $data['catatan_admin'],
            'diproses_oleh' => auth()->id(),
        ]);

        return redirect()->route('admin.usulan.index', ['status' => 'ditolak'])
            ->with('success', 'Usulan ditolak.');
    }
}