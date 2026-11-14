<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'program_studi',
    'deskripsi_keahlian',
    'foto_profil',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function keahlian(): BelongsToMany
    {
        return $this->belongsToMany(
            BidangKeahlian::class,
            'keahlian_dosen',
            'user_id',
            'bidang_keahlian_id',
            'id',
            'bidang_keahlian_id'
        )->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
