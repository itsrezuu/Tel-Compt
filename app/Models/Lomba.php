<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lomba extends Model
{
    protected $table = 'lomba';
    protected $primaryKey = 'lomba_id';

    protected $fillable = [
        'judul_lomba',
        'penyelenggara',
        'kategori',
        'tingkat',
        'tenggat_pendaftaran',
        'link_pendaftaran',
        'jenis_pendampingan',
        'status_publikasi',
        'created_by',
    ];
}
