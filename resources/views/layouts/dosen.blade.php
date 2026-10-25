<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Profil & Keahlian') · TEL-COMPT</title>

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
            --muted: #71809f;
            --line: #dfe6f1;
            --surface: #ffffff;
            --page: #f7f9fc;
            --sidebar-w: 222px;
            --topbar-h: 65px;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; min-height: 100%; }
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: var(--page);
            color: var(--ink);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        button, input, textarea, select { font: inherit; }
        button { cursor: pointer; }
        :focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

        /* Sidebar */
        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            padding: 28px 15px 24px;
            z-index: 40;
            overflow-y: auto;
            transition: transform .2s ease;
        }
        .brand { padding: 0 12px; margin-bottom: 24px; }
        .brand-name { font-size: 22px; font-weight: 700; letter-spacing: .2px; color: #fff; line-height: 1.2; }
        .brand-sub { font-size: 12px; color: var(--sidebar-text); margin-top: 5px; }
        .role-badge {
            background: var(--sidebar-active);
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 7px;
            margin-bottom: 27px;
        }
        .nav { display: flex; flex-direction: column; gap: 5px; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 40px;
            padding: 10px 12px;
            border-radius: 7px;
            color: var(--sidebar-text);
            font-size: 13px;
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
        .nav-link.active { background: var(--sidebar-active); color: #fff; font-weight: 600; }
        .sidebar-footer { margin-top: auto; padding: 24px 12px 0; border-top: 1px solid rgba(255,255,255,.15); }
        .sidebar-footer .name { color: #fff; font-weight: 600; font-size: 13px; }
        .sidebar-footer .meta { font-size: 12px; margin-top: 5px; }
        .logout-btn { background: none; border: 0; padding: 0; color: inherit; font-size: 12px; }
        .logout-btn:hover { color: #fff; text-decoration: underline; }

        /* Main content and topbar */
        .main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            height: var(--topbar-h);
            flex-shrink: 0;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .topbar-title { font-size: 13px; font-weight: 600; margin: 0; color: #1e2a4b; }
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
        .topbar-right { display: flex; align-items: center; gap: 15px; }
        .bell {
            display: inline-flex;
            width: 30px;
            height: 32px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--muted);
            background: none;
            border: 0;
        }
        .bell:hover { background: var(--page); color: var(--ink); }
        .bell svg { width: 19px; height: 19px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
        .profile { display: flex; align-items: center; gap: 12px; color: var(--muted); font-size: 12px; }
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #edf4ff;
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
            display: grid;
            place-items: center;
        }
        .content { flex: 1; width: 100%; padding: 31px 30px 20px; }
        .flash { border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 14px; }
        .flash.success { background: #e8f7ee; color: #14703a; border: 1px solid #bfe6cf; }
        .flash.error { background: #fdecec; color: #a62121; border: 1px solid #f5c2c2; }
        .overlay { display: none; position: fixed; inset: 0; background: rgba(15, 18, 50, .45); z-index: 35; }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            body.sidebar-open .sidebar { transform: translateX(0); }
            body.sidebar-open .overlay { display: block; }
            .main { margin-left: 0; }
            .menu-toggle { display: inline-flex; }
            .topbar { padding: 0 16px; }
            .content { padding: 24px 16px 18px; }
        }
        @media (max-width: 480px) {
            .content { padding: 20px 12px 16px; }
            .topbar-right { gap: 8px; }
            .profile { gap: 8px; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>

    @stack('styles')
</head>
<body>
@php
    $menus = [
        [
            'label' => 'Dasbor',
            'href' => url('/dosen/dashboard'),
            'match' => 'dosen/dashboard*',
            'icon' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        ],
        [
            'label' => 'Profil & Keahlian',
            'href' => url('/dosen/profil-keahlian'),
            'match' => 'dosen/profil-keahlian*',
            'icon' => '<path d="M8 21h8M12 17v4M7 4h10l-1 8-4 3-4-3-1-8Z"/><path d="M7 6H4v3c0 2 1 3 4 3M17 6h3v3c0 2-1 3-4 3"/>',
        ],
        [
            'label' => 'Permintaan Konsultasi',
            'href' => url('/dosen/permintaan-konsultasi'),
            'match' => 'dosen/permintaan-konsultasi*',
            'icon' => '<path d="M6 3h12a2 2 0 0 1 2 2v16l-8-4-8 4V5a2 2 0 0 1 2-2Z"/>',
        ],
        [
            'label' => 'Permintaan Bimbingan',
            'href' => url('/dosen/permintaan-bimbingan'),
            'match' => 'dosen/permintaan-bimbingan*',
            'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M12 18v-6M9 15h6"/>',
        ],
        [
            'label' => 'Riwayat',
            'href' => url('/dosen/riwayat'),
            'match' => 'dosen/riwayat*',
            'icon' => '<path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z"/>',
        ],
        [
            'label' => 'Notifikasi',
            'href' => url('/dosen/notifikasi'),
            'match' => 'dosen/notifikasi*',
            'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        ],
    ];

    $userName = auth()->user()->name ?? 'Dimas Arunika';
    $initials = collect(explode(' ', trim($userName)))
        ->filter()
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->take(2)
        ->implode('') ?: 'DA';
    $unread = $unreadNotifications ?? 0;
@endphp

<div class="overlay" id="sidebarOverlay" aria-hidden="true"></div>

<aside class="sidebar" id="dosenSidebar" aria-label="Navigasi Dosen">
    <div class="brand">
        <div class="brand-name">TEL-COMPT</div>
        <div class="brand-sub">Telkom University</div>
    </div>

    <div class="role-badge">Dosen</div>

    <nav class="nav" aria-label="Menu utama Dosen">
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
        <div class="name">{{ $userName }}</div>
        <div class="meta">
            Sistem Informasi ·
            @if (\Illuminate\Support\Facades\Route::has('logout'))
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="logout-btn">Keluar</button>
                </form>
            @else
                <a href="{{ url('/logout') }}" class="logout-btn">Keluar</a>
            @endif
        </div>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <button type="button" class="menu-toggle" id="dosenMenuToggle" aria-label="Buka menu" aria-controls="dosenSidebar" aria-expanded="false">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="topbar-title">@yield('page_title', 'Profil & Keahlian')</h1>
        </div>

        <div class="topbar-right">
            <a href="{{ url('/dosen/notifikasi') }}" class="bell" aria-label="Notifikasi{{ $unread ? ' ('.$unread.' belum dibaca)' : '' }}">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
                @if ($unread > 0)
                    <span class="notification-dot">{{ $unread > 99 ? '99+' : $unread }}</span>
                @endif
            </a>
            <div class="profile">
                <span>Dosen</span>
                <span class="avatar" aria-hidden="true">{{ $initials }}</span>
            </div>
        </div>
    </header>

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
        const body = document.body;
        const toggle = document.getElementById('dosenMenuToggle');
        const overlay = document.getElementById('sidebarOverlay');

        if (!toggle || !overlay) return;

        function setOpen(open) {
            body.classList.toggle('sidebar-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', function () {
            setOpen(!body.classList.contains('sidebar-open'));
        });
        overlay.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') setOpen(false);
        });
    })();
</script>

@stack('scripts')
</body>
</html>
