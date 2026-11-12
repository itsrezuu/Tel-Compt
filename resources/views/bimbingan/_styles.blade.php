{{-- CSS bersama halaman bimbingan dan konsultasi. Dimasukkan lewat include di dalam blok push styles pada tiap view. --}}
<style>
    .bm-page { max-width: 1200px; margin: 0 auto; display: grid; gap: 20px; color: var(--ink); }
    .bm-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .bm-head h2 { font-size: 28px; font-weight: 700; margin: 0 0 6px; color: var(--ink); letter-spacing: -.2px; }
    .bm-head p { margin: 0; color: var(--muted); font-size: 14px; }
    .bm-crumb { margin: 0 0 10px; font-size: 13px; color: var(--muted); }
    .bm-crumb a:hover { text-decoration: underline; }

    .bm-card { background: var(--surface); border: 1px solid var(--line); border-radius: 10px; padding: 24px; }
    .bm-card h3 { margin: 0 0 16px; font-size: 18px; font-weight: 700; color: var(--ink); }
    .bm-grid { display: grid; grid-template-columns: 3fr 2fr; gap: 20px; align-items: start; }
    .bm-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .bm-field { margin-bottom: 18px; }
    .bm-label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; }
    .bm-input, .bm-select, .bm-textarea { width: 100%; padding: 13px 16px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); color: var(--ink); font: inherit; }
    .bm-textarea { min-height: 110px; resize: vertical; }
    .bm-input:focus, .bm-select:focus, .bm-textarea:focus { outline: 2px solid var(--primary); outline-offset: 0; border-color: var(--primary); }
    .bm-hint { margin: 6px 0 0; font-size: 12px; color: var(--muted); }

    .bm-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .bm-btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 18px; border-radius: 8px; border: 1px solid transparent; font-weight: 600; font-size: 14px; transition: background .15s ease; }
    .bm-btn-primary { background: var(--primary); color: #fff; }
    .bm-btn-primary:hover { background: var(--primary-dark); }
    .bm-btn-danger { background: #dc2626; color: #fff; }
    .bm-btn-danger:hover { background: #b91c1c; }
    .bm-btn-ghost { background: var(--surface); color: var(--ink); border-color: var(--line); }
    .bm-btn-disabled { background: #e5e9f2; color: #8a94b0; cursor: not-allowed; }
    .bm-link { color: var(--primary); font-weight: 600; }
    .bm-link:hover { text-decoration: underline; }

    .bm-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .bm-tab { padding: 10px 16px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); color: var(--muted); font-size: 14px; }
    .bm-tab.active { border-color: var(--primary); background: #eef5ff; color: var(--primary); font-weight: 600; }

    .bm-search-label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; }
    .bm-search { position: relative; }
    .bm-search svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: var(--muted); stroke-width: 2; fill: none; stroke-linecap: round; pointer-events: none; }
    .bm-search input { width: 100%; padding: 13px 16px 13px 40px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); color: var(--ink); font: inherit; }
    .bm-search input:focus { outline: 2px solid var(--primary); outline-offset: 0; border-color: var(--primary); }

    .bm-table-wrap { background: var(--surface); border: 1px solid var(--line); border-radius: 10px; overflow-x: auto; }
    table.bm-table { width: 100%; border-collapse: collapse; min-width: 720px; }
    .bm-table thead th { text-align: left; font-size: 13px; font-weight: 500; color: var(--muted); padding: 16px 20px; background: #fbfcfe; }
    .bm-table tbody td { padding: 18px 20px; border-top: 1px solid var(--line); font-size: 14px; vertical-align: middle; }
    .bm-table tbody tr:first-child td { border-top: 0; }
    .bm-table tbody tr.selected td { background: #fbfcfe; }
    .bm-table .strong { font-weight: 600; }
    .bm-table .sub { display: block; font-size: 12px; color: var(--muted); font-weight: 400; margin-top: 3px; }
    .bm-table .empty { text-align: center; color: var(--muted); padding: 32px; }

    .bm-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
    .bm-badge.menunggu { background: #fffbeb; color: #9a5800; }
    .bm-badge.disetujui, .bm-badge.ditanggapi { background: #e8f7ee; color: #14803f; }
    .bm-badge.ditolak { background: #fdecec; color: #a62121; }

    .bm-banner { display: flex; gap: 12px; padding: 16px 20px; border-radius: 10px; }
    .bm-banner svg { width: 20px; height: 20px; flex: 0 0 20px; margin-top: 2px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .bm-banner strong { display: block; font-size: 15px; }
    .bm-banner p { margin: 3px 0 0; font-size: 13px; color: var(--muted); }
    .bm-banner.ok { background: #e8f7ee; color: #14803f; }
    .bm-banner.bad { background: #fdecec; color: #a62121; }
    .bm-banner.warn { background: #fffbeb; color: #9a5800; }

    .bm-title { margin: 0 0 8px; font-size: 20px; font-weight: 700; color: var(--ink); }
    .bm-text { margin: 0 0 12px; font-size: 14px; white-space: pre-line; }
    .bm-sub { margin: 14px 0 6px; font-size: 14px; font-weight: 700; }
    .bm-meta { margin: 8px 0 0; font-size: 12px; color: var(--muted); }
    .bm-box { border: 1px solid var(--line); border-radius: 8px; padding: 14px 16px; font-size: 14px; white-space: pre-line; }
    .bm-box.ok { background: #e8f7ee; border-color: #bfe6cf; }
    .bm-box.bad { background: #fdecec; border-color: #f5c2c2; }
    .bm-box strong { display: block; margin-bottom: 4px; }

    .bm-item { display: grid; gap: 10px; }
    .bm-item-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .bm-item-top strong { color: var(--ink); }
    .bm-sublist { display: grid; gap: 12px; border-top: 1px solid var(--line); margin-top: 20px; padding-top: 20px; }
    .bm-sublist h4 { margin: 0; font-size: 13px; font-weight: 600; color: var(--muted); }
    .bm-subitem { border: 1px solid var(--line); border-radius: 8px; padding: 16px; display: grid; gap: 8px; }
    .bm-empty { background: var(--surface); border: 1px dashed #c9d4e6; border-radius: 10px; padding: 32px; text-align: center; color: var(--muted); font-size: 14px; }
    .bm-errors { margin: 0; padding-left: 18px; }

    @media (max-width: 900px) {
        .bm-grid, .bm-row { grid-template-columns: 1fr; }
        .bm-head h2 { font-size: 24px; }
    }
</style>
