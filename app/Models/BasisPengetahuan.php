<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasisPengetahuan extends Model
{
    public const STATUS_DRAF = 'draf';

    public const STATUS_DIPUBLIKASIKAN = 'dipublikasikan';

    protected $table = 'basis_pengetahuan';

    protected $fillable = [
        'created_by',
        'judul',
        'kategori',
        'lomba',
        'kontributor',
        'ringkasan',
        'isi',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
