<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanPrestasi extends Model
{
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    public const STATUS_ARSIP_BELUM_DITINJAU = 'belum_ditinjau';

    public const STATUS_ARSIP_MENUNGGU = 'menunggu';

    public const STATUS_ARSIP_DISETUJUI = 'disetujui';

    public const STATUS_ARSIP_DITOLAK = 'ditolak';

    protected $table = 'pengajuan_prestasi';

    protected $primaryKey = 'pengajuan_id';

    protected $fillable = [
        'mahasiswa_id',
        'lomba',
        'prestasi_hasil',
        'tanggal_pencapaian',
        'peserta_tim',
        'bukti_pendukung_path',
        'keluaran_lomba_path',
        'judul_arsip',
        'pengalaman_proses',
        'pembelajaran_saran',
        'dokumen_pengalaman_path',
        'persetujuan_publikasi',
        'status',
        'status_arsip',
        'bukti_sesuai',
        'keluaran_lomba_tersedia',
        'pengalaman_layak',
        'catatan_keputusan',
        'publikasikan_prestasi',
        'publikasikan_arsip',
        'ditinjau_oleh',
        'ditinjau_pada',
    ];

    protected $casts = [
        'tanggal_pencapaian' => 'date',
        'persetujuan_publikasi' => 'boolean',
        'bukti_sesuai' => 'boolean',
        'keluaran_lomba_tersedia' => 'boolean',
        'pengalaman_layak' => 'boolean',
        'publikasikan_prestasi' => 'boolean',
        'publikasikan_arsip' => 'boolean',
        'ditinjau_pada' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function getNomorPengajuanAttribute(): string
    {
        return 'P-'.str_pad((string) $this->getKey(), 3, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }
}
