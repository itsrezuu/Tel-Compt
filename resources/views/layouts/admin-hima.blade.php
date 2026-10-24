<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin HIMA') · TEL-COMPT</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #32289b;
            --sidebar-active: rgba(255, 255, 255, .16);
            --sidebar-hover: rgba(255, 255, 255, .08);
            --sidebar-text: #d8d6f5;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --ink: #1b2559;
            --muted: #64709a;
            --line: #e5e8f0;
            --surface: #ffffff;
            --page: #f8f9fc;
            --sidebar-w: 232px;
            --topbar-h: 68px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: var(--page);
            color: var(--ink);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }
        button { font: inherit; cursor: pointer; }

        :focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            padding: 28px 16px 24px;
            z-index: 40;
            overflow-y: auto;
            transition: transform .2s ease;
        }

        .brand { padding: 0 12px; margin-bottom: 24px; }
        .brand-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: .2px;
            color: #fff;
            line-height: 1.2;
        }
        .brand-sub { font-size: 12px; color: var(--sidebar-text); margin-top: 4px; }

        .role-badge {
            background: var(--sidebar-active);
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            padding: 9px 12px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .18);
            margin-bottom: 28px;
        }

        .nav { display: flex; flex-direction: column; gap: 4px; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 8px;
            color: var(--sidebar-text);
            font-size: 14px;
            transition: background .15s ease, color .15s ease;
        }
        .nav-link svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .nav-link:hover { background: var(--sidebar-hover); color: #fff; }
        .nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .18);
        }

        .sidebar-footer { margin-top: auto; padding: 24px 12px 0; }
        .sidebar-footer .name { color: #fff; font-weight: 600; font-size: 14px; }
        .sidebar-footer .meta { font-size: 12px; margin-top: 4px; }
        .logout-btn {
            background: none;
            border: 0;
            padding: 0;
            color: inherit;
            font-size: 12px;
        }
        .logout-btn:hover { color: #fff; text-decoration: underline; }

        /* ---------- Main area ---------- */
        .main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            height: var(--topbar-h);
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-title { font-size: 15px; font-weight: 600; margin: 0; }

        .menu-toggle {
            display: none;
            background: none;
            border: 1px solid var(--line);
            border-radius: 8px;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            color: var(--ink);
        }
        .menu-toggle svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }

        .topbar-right { display: flex; align-items: center; gap: 16px; }

        .bell {
            position: relative;
            display: inline-flex;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--muted);
            background: none;
            border: 0;
        }
        .bell:hover { background: var(--page); color: var(--ink); }
        .bell svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
        .bell .dot {
            position: absolute;
            top: 6px;
            right: 6px;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 999px;
            background: #dc2626;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            line-height: 16px;
            text-align: center;
            border: 2px solid var(--surface);
            box-sizing: content-box;
            transform: translate(25%, -15%);
        }

        .profile { display: flex; align-items: center; gap: 10px; color: var(--muted); }
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e0e9ff;
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
            display: grid;
            place-items: center;
        }

        .content { flex: 1; padding: 32px; }

        .flash {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .flash.success { background: #e8f7ee; color: #14703a; border: 1px solid #bfe6cf; }
        .flash.error { background: #fdecec; color: #a62121; border: 1px solid #f5c2c2; }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 18, 50, .45);
            z-index: 35;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            body.sidebar-open .sidebar { transform: translateX(0); }
            body.sidebar-open .overlay { display: block; }
            .main { margin-left: 0; }
            .menu-toggle { display: inline-flex; }
            .topbar { padding: 0 16px; }
            .content { padding: 20px 16px; }
            .profile .profile-name { display: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>

    {{-- CSS tambahan khusus per halaman --}}
    @stack('styles')
</head>
<body>

@php
    // Menu navigasi sidebar. 'match' = pola URL agar menu otomatis aktif.
    // Sesuaikan 'href' & 'match' dengan route di routes/web.php kamu.
    $menus = [
        [
            'label' => 'Dasbor',
            'href'  => url('/admin-hima/dashboard'),
            'match' => 'admin-hima/dashboard*',
            'icon'  => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        ],
        [
            'label' => 'Kelola Lomba',
            'href'  => url('/admin-hima/lomba'),
            'match' => 'admin-hima/lomba*',
            'icon'  => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
        ],
        [
            'label' => 'Tinjau Usulan',
            'href'  => url('/admin-hima/usulan'),
            'match' => 'admin-hima/usulan*',
            'icon'  => '<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>',
        ],
        [
            'label' => 'Verifikasi & Arsip',
            'href'  => url('/admin-hima/verifikasi'),
            'match' => 'admin-hima/verifikasi*',
            'icon'  => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/>',
        ],
        [
            'label' => 'Kelola Konten',
            'href'  => url('/admin-hima/konten'),
            'match' => 'admin-hima/konten*',
            'icon'  => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
        ],
        [
            'label' => 'Notifikasi',
            'href'  => url('/admin-hima/notifikasi'),
            'match' => 'admin-hima/notifikasi*',
            'icon'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        ],
    ];

    // Data user (fallback jika belum ada auth)
    $userName   = auth()->user()->name ?? 'Admin HIMA';
    $initials   = collect(explode(' ', trim($userName)))
                    ->filter()
                    ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                    ->take(2)
                    ->implode('') ?: 'HM';

    // Jumlah notifikasi belum dibaca (kirim dari controller / view composer jika ada)
    $unread = $unreadNotifications ?? 0;
@endphp

<div class="overlay" id="overlay" aria-hidden="true"></div>

{{-- ===================== SIDEBAR ===================== --}}
<aside class="sidebar" id="sidebar" aria-label="Navigasi utama">
    <div class="brand">
        <div class="brand-name">TEL-COMPT</div>
        <div class="brand-sub">Telkom University</div>
    </div>

    <div class="role-badge">Admin HIMA</div>

    <nav class="nav">
        @foreach ($menus as $menu)
            <a href="{{ $menu['href'] }}"
               class="nav-link {{ request()->is($menu['match']) ? 'active' : '' }}"
               @if (request()->is($menu['match'])) aria-current="page" @endif>
                <svg viewBox="0 0 24 24" aria-hidden="true">{!! $menu['icon'] !!}</svg>
                <span>{{ $menu['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="name">Pengelola HIMA</div>
        <div class="meta">
            Sistem Informasi ·
            <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : url('/logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="logout-btn">Keluar</button>
            </form>
        </div>
    </div>
</aside>

{{-- ===================== MAIN ===================== --}}
<div class="main">

    {{-- Topbar --}}
    <header class="topbar">
        <div class="topbar-left">
            <button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka menu" aria-controls="sidebar">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="topbar-title">@yield('page_title', 'Dasbor')</h1>
        </div>

        <div class="topbar-right">
            <a href="{{ url('/admin-hima/notifikasi') }}" class="bell" aria-label="Notifikasi{{ $unread ? ' ('.$unread.' belum dibaca)' : '' }}">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
                @if ($unread > 0)
                    <span class="dot">{{ $unread > 99 ? '99+' : $unread }}</span>
                @endif
            </a>

            <div class="profile">
                <span class="profile-name">{{ $userName }}</span>
                <span class="avatar" aria-hidden="true">{{ $initials }}</span>
            </div>
        </div>
    </header>

    {{-- Isi halaman (berubah-ubah per halaman) --}}
    <main class="content">
        @if (session('success'))
            <div class="flash success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash error" role="alert">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    (function () {
        var body = document.body;
        var toggle = document.getElementById('menuToggle');
        var overlay = document.getElementById('overlay');

        function setOpen(open) {
            body.classList.toggle('sidebar-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', function () {
            setOpen(!body.classList.contains('sidebar-open'));
        });
        overlay.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });
    })();
</script>

{{-- JS tambahan khusus per halaman --}}
@stack('scripts')
</body>
</html>