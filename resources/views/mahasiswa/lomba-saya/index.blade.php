@extends('layouts.mahasiswa')

@section('title', 'Lomba Saya')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Lomba Saya</h1>
        <p>Daftar kompetisi yang telah kamu simpan.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($daftarLomba->isEmpty())
        <div class="empty-state">
            <h3>Belum Ada Lomba Tersimpan</h3>
            <p>
                Kamu belum menyimpan kompetisi apa pun.
                Jelajahi katalog lomba untuk menemukan kompetisi yang menarik.
            </p>

            <a href="{{ route('mahasiswa.lomba.index') }}">
                Jelajahi Katalog Lomba
            </a>
        </div>
    @else
        <div class="comp-grid">
            @foreach ($daftarLomba as $lomba)
                @include('mahasiswa.lomba._card', [
                    'lomba' => $lomba,
                    'idTersimpan' => $idTersimpan,
                ])
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $daftarLomba->links() }}
        </div>
    @endif
</div>
@endsection
