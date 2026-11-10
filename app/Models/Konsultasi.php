<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Konsultasi extends Model
{
    public const MENUNGGU = 'menunggu';

    public const DITANGGAPI = 'ditanggapi';

    public const DITOLAK = 'ditolak';

    public const STATUSES = [self::MENUNGGU, self::DITANGGAPI, self::DITOLAK];

    protected $table = 'konsultasi';

    protected $primaryKey = 'konsultasi_id';

    protected $fillable = [
        'bimbingan_id', 'mahasiswa_id', 'dosen_id',
        'pertanyaan', 'jawaban_arahan', 'status', 'responded_at',
    ];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function bimbingan(): BelongsTo
    {
        return $this->belongsTo(Bimbingan::class, 'bimbingan_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    /** Contoh: K-018 */
    public function getKodeAttribute(): string
    {
        return 'K-'.str_pad((string) $this->getKey(), 3, '0', STR_PAD_LEFT);
    }
}
