{{-- Gaya bersama untuk halaman usulan (admin & mahasiswa). Di-include dari dalam @section('content'). --}}
@push('styles')
<style>
    .page-head h2 { font-size: 28px; font-weight: 700; margin: 0 0 6px; color: var(--ink); letter-spacing: -.2px; }
    .page-head p { margin: 0; color: var(--muted); font-size: 14px; }
    .page-head p a:hover { text-decoration: underline; }

    .card { background: var(--surface); border: 1px solid var(--line); border-radius: 12px; padding: 24px; margin-top: 24px; }
    .card h3 { margin: 0 0 16px; font-size: 18px; font-weight: 600; }
    .card .req-note { font-size: 12px; color: var(--muted); margin: -8px 0 20px; }

    .btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; border: 1px solid transparent; transition: background .15s ease, border-color .15s ease; }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-ghost { background: var(--surface); border-color: var(--line); color: var(--ink); }
    .btn-ghost:hover { border-color: var(--primary); }
    .btn-danger { background: #dc2626; color: #fff; }
    .btn-danger:hover { background: #b91c1c; }
    .actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

    /* Tab status */
    .tabs { display: flex; gap: 12px; flex-wrap: wrap; margin: 24px 0; }
    .tab { padding: 12px 14px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); font-size: 14px; color: var(--muted); }
    .tab:hover { border-color: var(--primary); }
    .tab.active { border-color: var(--primary); color: var(--primary); font-weight: 600; }

    /* Pencarian */
    .search-label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; }
    .search-box { position: relative; margin-bottom: 24px; }
    .search-box svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: var(--muted); stroke-width: 2; fill: none; stroke-linecap: round; pointer-events: none; }
    .search-box input { width: 100%; padding: 13px 16px 13px 40px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); font: inherit; color: var(--ink); }
    .search-box input::placeholder { color: #7d88ab; }
    .search-box input:focus { outline: 2px solid var(--primary); outline-offset: 0; border-color: var(--primary); }

    /* Tabel */
    .table-wrap { background: var(--surface); border: 1px solid var(--line); border-radius: 10px; overflow-x: auto; }
    table.data-table { width: 100%; border-collapse: collapse; min-width: 720px; }
    .data-table thead th { text-align: left; font-size: 13px; font-weight: 500; color: var(--muted); padding: 16px 20px; background: #fbfcfe; }
    .data-table tbody td { padding: 20px; border-top: 1px solid var(--line); font-size: 14px; vertical-align: middle; }
    .data-table tbody tr:first-child td { border-top: 0; }
    .data-table .judul { font-weight: 600; }
    .data-table .sub { display: block; font-size: 12px; color: var(--muted); font-weight: 400; margin-top: 2px; }
    .data-table a.link { color: var(--primary); font-weight: 500; }
    .data-table a.link:hover { text-decoration: underline; }
    .empty { text-align: center; color: var(--muted); padding: 40px 20px !important; }

    .table-foot { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 24px; flex-wrap: wrap; }
    .table-foot .info { font-size: 13px; color: var(--muted); }

    /* Badge status */
    .badge { display: inline-block; padding: 3px 9px; border-radius: 6px; font-size: 12px; font-weight: 600; }
    .badge.waiting   { background: #fff6dd; color: #a15c00; }
    .badge.process   { background: #e6f0ff; color: #1d4ed8; }
    .badge.published { background: #e8f7ee; color: #14803f; }
    .badge.rejected  { background: #fdecec; color: #b42318; }

    /* Form sederhana (mahasiswa & tolak) */
    .field { margin-bottom: 20px; }
    .field label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; }
    .field input[type="text"], .field input[type="url"], .field textarea {
        width: 100%; padding: 13px 14px; border: 1px solid var(--line); border-radius: 8px;
        background: var(--surface); font: inherit; color: var(--ink);
    }
    .field textarea { min-height: 96px; resize: vertical; line-height: 1.5; }
    .field input::placeholder, .field textarea::placeholder { color: #8a94b3; }
    .field input:focus, .field textarea:focus { outline: 2px solid var(--primary); outline-offset: 0; border-color: var(--primary); }
    .field.has-error input, .field.has-error textarea { border-color: #dc2626; }
    .field .error-text { color: #b42318; font-size: 12px; margin-top: 6px; }
    .field .hint { color: var(--muted); font-size: 12px; margin-top: 6px; }

    /* Banner hasil kirim */
    .banner { display: flex; gap: 12px; align-items: flex-start; border-radius: 8px; padding: 16px 20px; margin-bottom: 16px; }
    .banner svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 2px; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .banner .title { font-weight: 600; font-size: 14px; }
    .banner .desc { color: var(--muted); font-size: 13px; margin-top: 2px; }
    .banner.ok { background: #effaf3; } .banner.ok .title, .banner.ok svg { color: #14803f; stroke: #14803f; }
    .banner.no { background: #fdf0f0; } .banner.no .title, .banner.no svg { color: #b42318; stroke: #b42318; }
    .flow { font-size: 14px; margin: 16px 0 20px; }

    /* Detail usulan */
    .detail { display: grid; grid-template-columns: 180px 1fr; gap: 14px 20px; margin: 0; font-size: 14px; }
    .detail dt { color: var(--muted); }
    .detail dd { margin: 0; word-break: break-word; }
    .detail a { color: var(--primary); }
    .detail a:hover { text-decoration: underline; }
    .hint-text { color: var(--muted); font-size: 13px; margin: 0 0 16px; }

    @media (max-width: 700px) {
        .page-head h2 { font-size: 24px; }
        .detail { grid-template-columns: 1fr; gap: 4px; }
        .detail dd { margin-bottom: 12px; }
        .card { padding: 18px; }
    }
</style>
@endpush