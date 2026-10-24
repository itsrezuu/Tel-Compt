@extends('layouts.dosen')
@section('title', 'Permintaan Konsultasi')

@section('content')
    <div>
        <h1 class="text-2xl font-bold text-indigo-950">Permintaan konsultasi</h1>
        <p class="text-sm text-slate-500">Pertanyaan mahasiswa yang ditujukan kepada Anda.</p>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Kode</th>
                    <th class="px-4 py-3 font-medium">Mahasiswa</th>
                    <th class="px-4 py-3 font-medium">Pertanyaan</th>
                    <th class="px-4 py-3 font-medium">Diterima</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium">K-{{ str_pad($item->konsultasi_id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-3">{{ $item->mahasiswa->name }}</td>
                        <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($item->pertanyaan, 60) }}</td>
                        <td class="px-4 py-3">{{ $item->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3"><span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">{{ ucfirst($item->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada konsultasi masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
