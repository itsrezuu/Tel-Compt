@extends('layouts.admin-hima')

@section('title', 'Tambah Lomba')
@section('page_title', 'Kelola Lomba')

@section('content')
    <div class="page-head">
        <h2>Tambah lomba</h2>
        <p><a href="{{ route('admin.lomba.index') }}">Kelola Lomba</a> / Informasi kompetisi · Kurasi manual</p>
    </div>

    <div class="info-banner" role="note">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <div>
            <div class="title">Lomba baru belum dipublikasikan</div>
            <div class="desc">Isi informasi dari situs penyelenggara dan periksa sumber sebelum publikasi.</div>
        </div>
    </div>

    @include('admin-hima.lomba._form', [
        'lomba'       => null,
        'action'      => route('admin.lomba.store'),
        'method'      => 'POST',
        'submitLabel' => 'Publikasikan lomba',
    ])
@endsection