@php $disimpan = in_array($lomba->id, $idTersimpan ?? [], true); @endphp

<article class="card comp-card">
    <div class="comp-top">
        <span class="tag">{{ $lomba->kategori }} · {{ $lomba->tingkat }}</span>

        <form method="POST" action="{{ route('mahasiswa.lomba.simpan', $lomba) }}">
            @csrf
            <button type="submit" class="bookmark {{ $disimpan ? 'on' : '' }}"
                    aria-pressed="{{ $disimpan ? 'true' : 'false' }}"
                    aria-label="{{ $disimpan ? 'Hapus dari Lomba Saya' : 'Simpan ke Lomba Saya' }}: {{ $lomba->judul }}">
                <svg viewBox="0 0 24 24"><path d="M6 3h12v18l-6-4-6 4z"/>@if($disimpan)<path d="M9 9l2 2 4-4"/>@endif</svg>
            </button>
        </form>
    </div>

    <h2 class="comp-title">{{ $lomba->judul }}</h2>
    <p class="comp-org">{{ $lomba->penyelenggara }}</p>

    <p class="comp-deadline">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v16H3zM3 10h18M8 3v4M16 3v4"/></svg>
        Batas daftar · {{ $lomba->batas_label }}
    </p>
    <p class="comp-guidance">{{ $lomba->pendampingan_label }}</p>

    <div class="comp-actions">
        <a href="{{ route('mahasiswa.lomba.show', $lomba) }}" class="btn btn-outline">Lihat detail</a>
        <a href="{{ $lomba->link_pendaftaran }}" class="ext-link" target="_blank" rel="noopener noreferrer">
            Situs resmi <span aria-hidden="true">↗</span> {{ $lomba->situs_host }}
        </a>
    </div>
</article>
