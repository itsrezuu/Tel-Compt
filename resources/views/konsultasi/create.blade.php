@extends('layouts.mahasiswa')
@section('title', 'Ajukan konsultasi')

@section('content')
    <div>
        <h1 class="text-2xl font-bold text-indigo-950">Ajukan konsultasi</h1>
        <p class="text-sm text-slate-500">Pertanyaan singkat kepada dosen yang bidang keahliannya relevan.</p>
    </div>

    <form method="POST" action="{{ route('konsultasi.store') }}" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
        @csrf

        <div>
            <label for="dosen_id" class="text-sm font-medium">Dosen tujuan *</label>
            <select id="dosen_id" name="dosen_id" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Pilih dosen</option>
                @foreach ($dosenList as $dosen)
                    <option value="{{ $dosen->id }}" @selected(old('dosen_id') == $dosen->id)>{{ $dosen->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="pertanyaan" class="text-sm font-medium">Pertanyaan *</label>
            <textarea id="pertanyaan" name="pertanyaan" rows="4" required maxlength="1000"
                      class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('pertanyaan') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Jelaskan konteks dan pertanyaan utama.</p>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Kirim konsultasi</button>
            <a href="{{ route('konsultasi.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium">Batal</a>
        </div>
    </form>
@endsection
