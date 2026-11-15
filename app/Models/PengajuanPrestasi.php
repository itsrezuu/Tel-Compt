<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanPrestasi extends Model
{
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';

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
    ];

    protected $casts = [
        'tanggal_pencapaian' => 'date',
        'persetujuan_publikasi' => 'boolean',
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
