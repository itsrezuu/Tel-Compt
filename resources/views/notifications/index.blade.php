@extends($layout)

@section('title', 'Notifikasi')
@section('page_title', 'Notifikasi')

@section('content')
<style>
    .notification-center { max-width: 980px; margin: 0 auto; }
    .notification-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .notification-filters { display: flex; gap: 8px; flex-wrap: wrap; }
    .notification-filters a, .notification-action { border: 1px solid var(--line); border-radius: 8px; padding: 9px 13px; background: var(--surface); color: var(--ink); font: inherit; text-decoration: none; cursor: pointer; }
    .notification-filters a.active { border-color: var(--primary); background: var(--primary); color: #fff; }
    .notification-list { display: grid; gap: 12px; }
    .notification-card { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; padding: 18px; border: 1px solid var(--line); border-radius: 12px; background: var(--surface); }
    .notification-card.unread { border-left: 4px solid var(--primary); }
    .notification-card h2 { margin: 0 0 6px; font-size: 17px; }
    .notification-card p { margin: 0 0 8px; color: var(--muted); line-height: 1.5; }
    .notification-card time { color: var(--muted); font-size: 13px; }
    .notification-card-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .notification-card-actions a { color: var(--primary); font-weight: 600; }
    .notification-empty { padding: 36px 18px; border: 1px dashed var(--line); border-radius: 12px; text-align: center; color: var(--muted); }
</style>

<div class="notification-center">
    <div class="notification-toolbar">
        <nav class="notification-filters" aria-label="Filter notifikasi">
            @foreach (['all' => 'Semua', 'unread' => 'Belum dibaca', 'read' => 'Sudah dibaca'] as $value => $label)
                <a href="{{ route($routePrefix.'.index', ['filter' => $value]) }}"
                   class="{{ $filter === $value ? 'active' : '' }}"
                   @if ($filter === $value) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route($routePrefix.'.read-all') }}">
                @csrf
                <button class="notification-action" type="submit">Tandai semua dibaca</button>
            </form>
        @endif
    </div>

    <div class="notification-list">
        @forelse ($notifications as $notification)
            <article class="notification-card {{ $notification->read_at ? '' : 'unread' }}">
                <div>
                    <h2>{{ $notification->data['title'] ?? 'Notifikasi' }}</h2>
                    <p>{{ $notification->data['message'] ?? '' }}</p>
                    <time datetime="{{ $notification->created_at?->toIso8601String() }}">
                        {{ $notification->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') }}
                    </time>
                </div>

                <div class="notification-card-actions">
                    @if (! empty($notification->data['url']))
                        <a href="{{ $notification->data['url'] }}">Buka detail</a>
                    @endif
                    @if (! $notification->read_at)
                        <form method="POST" action="{{ route($routePrefix.'.read', ['notification' => $notification->id]) }}">
                            @csrf
                            <button class="notification-action" type="submit">Tandai dibaca</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="notification-empty">Belum ada notifikasi untuk filter ini.</div>
        @endforelse
    </div>

    <div style="margin-top: 20px">
        @include('partials.pager', ['paginator' => $notifications])
    </div>
</div>
@endsection
