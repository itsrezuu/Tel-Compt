{{-- Dua kartu: Topik bimbingan + Informasi tim. Variabel: $item (Bimbingan dengan relasi dosen) --}}
@use('App\Models\Bimbingan')
<div class="bm-grid">
    <section class="bm-card">
        <h3>Topik bimbingan</h3>
        <p class="bm-title">{{ $item->topik_judul }}</p>
        @if ($item->lomba_judul)
            <p class="bm-text">{{ $item->lomba_judul }}</p>
        @endif
        <p class="bm-text">{{ $item->ringkasan_kebutuhan }}</p>
        <p class="bm-sub">Rencana kegiatan</p>
        <p class="bm-text">{{ $item->rencana_kegiatan }}</p>
    </section>

    <section class="bm-card">
        <h3>Informasi tim</h3>
        <p class="bm-sub" style="margin-top:0">{{ $item->nama_tim }}</p>
        <p class="bm-text" style="margin-bottom:4px">Ketua: {{ $item->ketua_tim }}</p>
        @foreach (preg_split('/\r\n|\r|\n/', (string) $item->anggota_tim) as $anggota)
            @if (trim($anggota) !== '')
                <p class="bm-text" style="margin-bottom:4px">{{ trim($anggota) }}</p>
            @endif
        @endforeach
        <p class="bm-meta">Dikirim {{ Bimbingan::formatWaktu($item->created_at) }} · Dosen tujuan: {{ $item->dosen->name }}</p>
    </section>
</div>
