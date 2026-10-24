@extends('layouts.mahasiswa')
@section('title', 'Konsultasi')

@section('content')
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-indigo-950">Konsultasi saya</h1>
            <p class="text-sm text-slate-500">Pertanyaan singkat kepada dosen dan statusnya.</p>
        </div>
        <a href="{{ route('konsultasi.create') }}" class="shrink-0 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Ajukan konsultasi</a>
    </div>

    @forelse ($items as $item)
        <article class="space-y-2 rounded-lg border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between gap-2">
                <p class="font-semibold">K-{{ str_pad($item->konsultasi_id, 3, '0', STR_PAD_LEFT) }}</p>
                <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">{{ ucfirst($item->status) }}</span>
            </div>
            <p class="text-sm">{{ $item->pertanyaan }}</p>
            <p class="text-xs text-slate-500">Dikirim {{ $item->created_at->format('d M Y, H.i') }} · Dosen tujuan: {{ $item->dosen->name }}</p>
        </article>
    @empty
        <div class="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
            Belum ada konsultasi. Klik "Ajukan konsultasi" untuk mulai.
        </div>
    @endforelse
@endsection
