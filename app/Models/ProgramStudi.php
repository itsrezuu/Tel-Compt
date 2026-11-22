<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramStudi extends Model
{
    protected $table = 'program_studi';

    protected $fillable = [
        'nama_prodi',
        'jenjang',
        'kampus',
    ];

    public function mahasiswa(): HasMany
    {
        return $this->hasMany(User::class, 'program_studi_id');
    }
}
