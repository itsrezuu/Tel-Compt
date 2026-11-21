{{-- Empat kartu ringkasan. Variabel: $stat --}}
<div class="dsh-stats">
    <div class="dsh-stat">
        <div class="label">Konsultasi ditanggapi</div>
        <div class="value">{{ $stat['konsultasi_ditanggapi'] }}</div>
        <div class="sub">{{ $stat['jumlah_dosen'] }} dosen pada periode ini</div>
    </div>
    <div class="dsh-stat">
        <div class="label">Bimbingan diproses</div>
        <div class="value">{{ $stat['bimbingan_diproses'] }}</div>
        <div class="sub">
            {{ $stat['bimbingan_setuju'] }} setuju · {{ $stat['bimbingan_tolak'] }} tolak
            @if ($stat['bimbingan_otomatis'] > 0)
                · {{ $stat['bimbingan_otomatis'] }} tidak direspons
            @endif
        </div>
    </div>
    <div class="dsh-stat">
        <div class="label">Waktu tanggapan</div>
        <div class="value">{{ $stat['rata'] }}</div>
        <div class="sub">Rata-rata permintaan</div>
    </div>
    <div class="dsh-stat">
        <div class="label">Keahlian lengkap</div>
        <div class="value">{{ $stat['lengkap'] }} / {{ $stat['jumlah_dosen'] }}</div>
        <div class="sub">Dosen memiliki tag</div>
    </div>
</div>
