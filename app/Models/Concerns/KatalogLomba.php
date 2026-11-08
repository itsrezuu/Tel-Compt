<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tambahkan ke model Lomba:  use \App\Models\Concerns\KatalogLomba;
 *
 * ASUMSI KOLOM tabel `lomba` (ubah di sini + di view jika berbeda):
 *   judul, penyelenggara, kategori, tingkat, jenis_pendampingan,
 *   batas_pendaftaran (date), deskripsi, link_pendaftaran, status
 */
trait KatalogLomba
{
    public const KATEGORI = ['Teknologi', 'Desain', 'Bisnis', 'Karya tulis', 'Riset'];
    public const TINGKAT = ['Regional', 'Nasional', 'Internasional'];
    public const PENDAMPINGAN = [
        'formal'   => 'Bimbingan formal',
        'opsional' => 'Konsultasi opsional',
        'tanpa'    => 'Tanpa pendampingan',
    ];
    /** Nilai kolom `status` untuk lomba yang sudah dikurasi HIMA. */
    public const STATUS_TAYANG = 'published';

    public function scopeTayang(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_TAYANG);
    }

    public function scopeMasihBuka(Builder $q): Builder
    {
        return $q->whereDate('batas_pendaftaran', '>=', now()->toDateString());
    }

    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $q;
        }
        $like = '%' . addcslashes(trim($kata), '%_\\') . '%';

        return $q->where(fn (Builder $w) => $w
            ->where('judul', 'like', $like)
            ->orWhere('penyelenggara', 'like', $like));
    }

    public function scopeSaring(Builder $q, array $f): Builder
    {
        return $q
            ->when($f['kategori'] ?? null, fn ($x, $v) => $x->where('kategori', $v))
            ->when($f['tingkat'] ?? null, fn ($x, $v) => $x->where('tingkat', $v))
            ->when($f['pendampingan'] ?? null, fn ($x, $v) => $x->where('jenis_pendampingan', $v));
    }

    public function scopeUrutkan(Builder $q, ?string $urut): Builder
    {
        return $urut === 'terbaru'
            ? $q->latest('created_at')
            : $q->orderBy('batas_pendaftaran')->orderBy('id');
    }

    public function getBatasLabelAttribute(): string
    {
        return Carbon::parse($this->batas_pendaftaran)->locale('id')->translatedFormat('d M Y');
    }

    public function getPendampinganLabelAttribute(): string
    {
        return self::PENDAMPINGAN[$this->jenis_pendampingan] ?? '-';
    }

    public function getSitusHostAttribute(): string
    {
        return parse_url($this->link_pendaftaran, PHP_URL_HOST) ?: (string) $this->link_pendaftaran;
    }
}
