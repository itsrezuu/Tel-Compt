<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Dipakai model Lomba:  use \App\Models\Concerns\KatalogLomba;
 *
 * Mengikuti tabel `lomba` milik Admin HIMA (SCRUM-23):
 *   lomba_id, judul_lomba, penyelenggara, kategori, tingkat,
 *   tenggat_pendaftaran (date), link_pendaftaran, deskripsi,
 *   jenis_pendampingan, status_publikasi
 */
trait KatalogLomba
{
    // Samakan dengan pilihan di form Admin HIMA (admin-hima/lomba/_form.blade.php)
    public const KATEGORI = ['Teknologi', 'Desain', 'Bisnis', 'Karya tulis'];
    public const TINGKAT = ['Nasional', 'Regional', 'Internasional', 'Internal kampus'];
    public const PENDAMPINGAN = [
        'tanpa pendampingan'     => 'Tanpa pendampingan',
        'konsultasi ringan'      => 'Konsultasi ringan',
        'pembimbing resmi wajib' => 'Pembimbing resmi wajib',
    ];
    /** Nilai `status_publikasi` untuk lomba yang tampil di katalog. */
    public const STATUS_TAYANG = 'dipublikasikan';

    public function scopeTayang(Builder $q): Builder
    {
        return $q->where('status_publikasi', self::STATUS_TAYANG);
    }

    public function scopeMasihBuka(Builder $q): Builder
    {
        return $q->whereDate('tenggat_pendaftaran', '>=', now()->toDateString());
    }

    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $q;
        }
        $like = '%' . addcslashes(trim($kata), '%_\\') . '%';

        return $q->where(fn (Builder $w) => $w
            ->where('judul_lomba', 'like', $like)
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
            : $q->orderBy('tenggat_pendaftaran')->orderBy($q->getModel()->getKeyName());
    }

    public function getBatasLabelAttribute(): string
    {
        return Carbon::parse($this->tenggat_pendaftaran)->locale('id')->translatedFormat('d M Y');
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
