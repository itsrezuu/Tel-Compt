@extends('layouts.mahasiswa')
@section('title', $article->judul)
@section('page_title', 'Basis Pengetahuan')

@push('styles')
<style>
.pd-wrap{max-width:920px}
.pd-back{display:inline-flex;margin-bottom:20px;color:var(--primary);font-weight:600}
.pd-card{padding:28px;border:1px solid var(--line);border-radius:11px;background:#fff}
.pd-tag{display:inline-flex;padding:4px 9px;border-radius:6px;background:#effbf3;color:#14803f;font-size:12px;font-weight:600}
.pd-card h2{margin:16px 0 10px;font-size:28px;line-height:1.35}
.pd-meta{color:var(--muted);font-size:13px}
.pd-summary{margin:22px 0;padding:16px 18px;border-left:3px solid var(--primary);background:#f7f9fc;line-height:1.6}
.pd-body{line-height:1.8;overflow-wrap:anywhere}
@media(max-width:640px){.pd-card{padding:20px}.pd-card h2{font-size:23px}}
</style>
@endpush

@section('content')
<div class="pd-wrap">
    <a class="pd-back" href="{{ route('mahasiswa.pengetahuan.index') }}">
        ← Kembali ke Pengetahuan &amp; Prestasi
    </a>

    <article class="pd-card">
        <span class="pd-tag">{{ $article->kategori }} · Dipublikasikan</span>
        <h2>{{ $article->judul }}</h2>
        <p class="pd-meta">
            {{ $article->kontributor ?: 'Tim Tel-Compt' }}
            @if ($article->lomba) · {{ $article->lomba }} @endif
            · {{ optional($article->published_at)->translatedFormat('d M Y') }}
        </p>
        <p class="pd-summary">{{ $article->ringkasan }}</p>
        <div class="pd-body">{!! nl2br(e($article->isi)) !!}</div>
    </article>
</div>
@endsection
