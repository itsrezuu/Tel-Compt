@extends('layouts.admin-hima')

@section('title', 'Edit Lomba')
@section('page_title', 'Kelola Lomba')

@section('content')
    <div class="page-head">
        <h2>Edit lomba</h2>
        <p><a href="{{ route('admin.lomba.index') }}">Kelola Lomba</a> / {{ $lomba->judul_lomba }}</p>
    </div>

    @include('admin-hima.lomba._form', [
        'lomba'       => $lomba,
        'action'      => route('admin.lomba.update', $lomba),
        'method'      => 'PUT',
        'submitLabel' => 'Simpan perubahan',
    ])
@endsection