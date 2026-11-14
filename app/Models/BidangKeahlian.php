<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BidangKeahlian extends Model
{
    protected $table = 'bidang_keahlian';

    protected $primaryKey = 'bidang_keahlian_id';

    protected $fillable = [
        'nama_bidang',
    ];

    public function dosen(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'keahlian_dosen',
            'bidang_keahlian_id',
            'user_id',
            'bidang_keahlian_id',
            'id'
        )->withTimestamps();
    }
}
