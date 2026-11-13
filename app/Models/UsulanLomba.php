<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsulanLomba extends Model
{
    protected $table = 'usulan_lomba';
    protected $primaryKey = 'usulan_id';

    public const STATUSES = ['menunggu', 'diproses', 'dipublikasikan', 'ditolak'];

    protected $fillable = [
        'pengaju_id',
        'judul_lomba',
        'link_resmi',
        'deskripsi',
        'status',
        'catatan_admin',
        'lomba_id',
        'diproses_oleh',
    ];

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'pengaju_id');
    }

    public function lomba()
    {
        return $this->belongsTo(Lomba::class, 'lomba_id', 'lomba_id');
    }

    // U-026 dst. (dihitung dari ID, tidak perlu kolom tambahan)
    public function getNomorAttribute(): string
    {
        return 'U-' . str_pad((string) $this->usulan_id, 3, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'menunggu'       => 'Menunggu',
            'diproses'       => 'Diproses',
            'dipublikasikan' => 'Dipublikasikan',
            'ditolak'        => 'Ditolak',
        ][$this->status] ?? ucfirst((string) $this->status);
    }

    public function getBadgeClassAttribute(): string
    {
        return [
            'menunggu'       => 'waiting',
            'diproses'       => 'process',
            'dipublikasikan' => 'published',
            'ditolak'        => 'rejected',
        ][$this->status] ?? 'waiting';
    }
}