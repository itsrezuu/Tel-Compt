<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Konsultasi extends Model
{
    protected $table = 'konsultasi';

    protected $primaryKey = 'konsultasi_id';

    protected $fillable = [
        'mahasiswa_id', 'dosen_id', 'lomba_id',
        'pertanyaan', 'jawaban_arahan', 'status', 'responded_at',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}