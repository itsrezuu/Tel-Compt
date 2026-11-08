<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lomba extends Model
{
    use \App\Models\Concerns\KatalogLomba;

    protected $table = 'lomba';

    protected $fillable = [
        'judul',
        'penyelenggara',
        'kategori',
        'tingkat',
        'jenis_pendampingan',
        'batas_pendaftaran',
        'deskripsi',
        'link_pendaftaran',
        'status',
    ];
}
