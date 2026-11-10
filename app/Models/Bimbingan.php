<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Bimbingan extends Model
{
    public const MENUNGGU = 'menunggu';

    public const DISETUJUI = 'disetujui';

    public const DITOLAK = 'ditolak';

    public const STATUSES = [self::MENUNGGU, self::DISETUJUI, self::DITOLAK];

    /** Batas waktu dosen merespons (jam). */
    public const BATAS_RESPONS_JAM = 24;

    protected $table = 'bimbingan';

    protected $primaryKey = 'bimbingan_id';

    protected $fillable = [
        'mahasiswa_id', 'dosen_id', 'lomba_id',
        'topik_judul', 'ringkasan_kebutuhan', 'nama_tim', 'ketua_tim',
        'anggota_tim', 'rencana_kegiatan',
        'status', 'catatan_dosen', 'ditolak_otomatis',
        'batas_respons_at', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'ditolak_otomatis' => 'boolean',
            'batas_respons_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function konsultasi(): HasMany
    {
        return $this->hasMany(Konsultasi::class, 'bimbingan_id');
    }

    /** Jumlah anggota = ketua + jumlah baris pada kolom anggota. */
    public function getAnggotaJumlahAttribute(): int
    {
        $baris = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $this->anggota_tim)));

        return count($baris) + 1;
    }

    /** Pengajuan yang masih "mengunci" mahasiswa: menunggu atau disetujui. */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereIn('status', [self::MENUNGGU, self::DISETUJUI]);
    }

    /** Contoh: B-012 */
    public function getKodeAttribute(): string
    {
        return 'B-'.str_pad((string) $this->getKey(), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Judul lomba terkait. Tabel lomba (SCRUM-23) belum tentu ada di database ini,
     * jadi dicek dulu dan dikembalikan null kalau belum ada.
     */
    public function getLombaJudulAttribute(): ?string
    {
        if (! $this->lomba_id || ! Schema::hasTable('lomba')) {
            return null;
        }

        return DB::table('lomba')->where('lomba_id', $this->lomba_id)->value('judul_lomba');
    }

    /**
     * Format tanggal Indonesia dalam WIB, contoh "09 Okt 2026" atau "10 Okt 2026, 09.10".
     */
    public static function formatWaktu(?CarbonInterface $waktu, bool $denganJam = false): string
    {
        if ($waktu === null) {
            return '-';
        }

        return $waktu->copy()
            ->setTimezone('Asia/Jakarta')
            ->locale('id')
            ->translatedFormat($denganJam ? 'd M Y, H.i' : 'd M Y');
    }

    /**
     * Tolak otomatis semua pengajuan yang sudah lewat batas respons.
     * Dipanggil oleh scheduler (tiap menit) dan di awal setiap halaman/aksi bimbingan.
     */
    public static function tolakKadaluarsa(): int
    {
        return static::where('status', self::MENUNGGU)
            ->where('batas_respons_at', '<=', now())
            ->update([
                'status' => self::DITOLAK,
                'ditolak_otomatis' => true,
                'catatan_dosen' => 'Ditolak otomatis: dosen tidak merespons dalam '.(self::BATAS_RESPONS_JAM / 24).' hari.',
                'responded_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
