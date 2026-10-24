@php
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TEL-COMPT')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<div class="flex min-h-screen flex-col md:flex-row">
    <aside class="bg-indigo-900 px-4 py-5 text-indigo-100 md:w-60 md:shrink-0">
        <p class="px-2 text-xl font-bold tracking-wide text-white">TEL-COMPT</p>
        <p class="px-2 text-xs text-indigo-300">Telkom University</p>
        <p class="mt-5 rounded-md bg-indigo-700/60 px-3 py-2 text-sm">Dosen</p>
        <nav class="mt-4">
            <a href="{{ route('dosen.konsultasi.index') }}" class="block rounded-md bg-indigo-700 px-3 py-2 text-sm font-medium text-white">Permintaan Konsultasi</a>
        </nav>
        <p class="mt-6 border-t border-indigo-700 px-2 pt-4 text-sm font-medium text-white">{{ $user->name }}</p>
        <p class="px-2 text-xs text-indigo-300">Sistem Informasi</p>
    </aside>

    <main class="mx-auto w-full max-w-4xl space-y-5 p-6">
        @if (session('status'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
