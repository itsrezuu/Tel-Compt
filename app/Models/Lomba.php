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
        'pendaftaran_dibuka',
        'tenggat_pendaftaran',
        'batas_pengumpulan',
        'tanggal_final',
        'link_pendaftaran',
        'deskripsi',
        'jenis_pendampingan',
        'status_publikasi',
        'created_by',
    ];

    protected $casts = [
        'pendaftaran_dibuka'  => 'date',
        'tenggat_pendaftaran' => 'date',
        'batas_pengumpulan'   => 'date',
        'tanggal_final'       => 'date',
    ];
}