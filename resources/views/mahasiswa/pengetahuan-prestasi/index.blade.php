@extends('layouts.mahasiswa')
@section('title', 'Pengetahuan & Prestasi')
@section('page_title', 'Pengetahuan & Prestasi')

@push('styles')
<style>
.pp-head h2{margin:0 0 6px;font-size:27px;letter-spacing:-.3px}
.pp-head p{margin:0 0 24px;color:var(--muted)}
.pp-search{display:grid;gap:8px;margin-bottom:24px}
.pp-search label{font-weight:600}
.pp-search input{width:100%;padding:13px 14px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font:inherit}
.pp-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start}
.pp-card{padding:24px;border:1px solid var(--line);border-radius:11px;background:#fff;min-width:0}
.pp-card h3{margin:0 0 17px;font-size:18px}
.pp-entry{padding:0 0 18px;margin:0 0 18px;border-bottom:1px solid var(--line)}
.pp-entry:last-child{padding:0;margin:0;border:0}
.pp-tag{display:inline-flex;padding:4px 9px;border-radius:6px;background:#effbf3;color:#14803f;font-size:12px;font-weight:600}
.pp-entry h4{margin:14px 0 10px;font-size:17px;line-height:1.4}
.pp-entry .meta{margin:0 0 14px;color:var(--muted);font-size:12px}
.pp-entry .summary{margin:0 0 14px;line-height:1.55}
.pp-entry .rank{font-size:24px;color:var(--ink);margin:16px 0 8px}
.pp-entry .competition{font-size:17px;margin:0 0 10px}
.pp-btn{display:inline-flex;align-items:center;min-height:43px;padding:0 15px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font-weight:600}
.pp-btn:hover{border-color:var(--primary);color:var(--primary)}
.pp-empty{padding:20px 0;color:var(--muted)}
.pp-pager{display:flex;justify-content:flex-end;margin-top:18px}
.pp-foot{margin-top:26px;color:var(--muted);font-size:12px}
.pp-footer{margin-top:38px;color:var(--muted);font-size:11px}
@media(max-width:900px){.pp-grid{grid-template-columns:1fr}}
@media(max-width:640px){.pp-head h2{font-size:23px}.pp-card{padding:19px}}
</style>
@endpush

@section('content')
<header class="pp-head">
    <h2>Basis Pengetahuan &amp; Galeri Prestasi</h2>
    <p>Pembelajaran dan pencapaian yang telah ditinjau HIMA.</p>
</header>

<form class="pp-search" method="GET" action="{{ route('mahasiswa.pengetahuan.index') }}">
    <label for="q">Cari pengetahuan atau prestasi</label>
    <input id="q" name="q" type="search" value="{{ $search }}"
           placeholder="Cari judul, lomba atau nama mahasiswa">
</form>

<div class="pp-grid">
    <section class="pp-card" aria-labelledby="pp-knowledge-title">
        <h3 id="pp-knowledge-title">Basis Pengetahuan</h3>

        @forelse ($articles as $article)
            <article class="pp-entry">
                <span class="pp-tag">Dipublikasikan</span>
                <h4>{{ $article->judul }}</h4>
                <p class="meta">
                    {{ $article->kontributor ?: 'Tim Tel-Compt' }} · {{ $article->kategori }}
                    · {{ optional($article->published_at)->translatedFormat('d M Y') }}
                </p>
                <p class="summary">{{ $article->ringkasan }}</p>
                <a class="pp-btn" href="{{ route('mahasiswa.pengetahuan.artikel', $article) }}">
                    Baca arsip pengalaman
                </a>
            </article>
        @empty
            <div class="pp-empty">
                {{ $search ? 'Tidak ada arsip yang cocok dengan pencarian.' : 'Belum ada artikel yang dipublikasikan.' }}
            </div>
        @endforelse

        @if ($articles->hasPages())
            <div class="pp-pager">
                @include('partials.pager', ['paginator' => $articles])
            </div>
        @endif
    </section>

    <section class="pp-card" aria-labelledby="pp-achievement-title">
        <h3 id="pp-achievement-title">Galeri Prestasi</h3>

        @forelse ($achievements as $achievement)
            <article class="pp-entry">
                <span class="pp-tag">Terverifikasi</span>
                <h4 class="rank">{{ $achievement->prestasi_hasil }}</h4>
                <h5 class="competition">{{ $achievement->lomba }}</h5>
                <p class="meta">
                    {{ $achievement->mahasiswa?->name ?? $achievement->peserta_tim }}
                    · {{ $achievement->peserta_tim }}
                </p>
                <p class="meta">
                    {{ $achievement->tanggal_pencapaian?->translatedFormat('d M Y') }}
                    · Diverifikasi HIMA {{ $achievement->ditinjau_pada?->translatedFormat('d M Y') }}
                </p>
                <p class="summary">
                    {{ \Illuminate\Support\Str::limit($achievement->pembelajaran_saran, 220) }}
                </p>
                <a class="pp-btn" href="{{ route('mahasiswa.pengetahuan.prestasi', $achievement) }}">
                    Lihat prestasi &amp; keluaran
                </a>
            </article>
        @empty
            <div class="pp-empty">
                {{ $search ? 'Tidak ada prestasi yang cocok dengan pencarian.' : 'Belum ada prestasi terverifikasi yang ditampilkan.' }}
            </div>
        @endforelse

        @if ($achievements->hasPages())
            <div class="pp-pager">
                @include('partials.pager', ['paginator' => $achievements])
            </div>
        @endif
    </section>
</div>

<p class="pp-foot">
    Semua identitas, kompetisi dan hasil merupakan data yang disetujui untuk publikasi oleh pemilik dan telah ditinjau HIMA.
</p>
<p class="pp-footer">TEL-COMPT · Referensi pengalaman dan pencapaian mahasiswa.</p>
@endsection
