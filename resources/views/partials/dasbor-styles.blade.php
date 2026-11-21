{{-- CSS bersama dasbor Admin HIMA dan Kaprodi (SCRUM-33, SCRUM-35). Dimasukkan lewat include di blok push styles. --}}
<style>
    .dsh-page { max-width: 1240px; margin: 0 auto; display: grid; gap: 22px; }
    .dsh-head h2 { margin: 0 0 6px; font-size: 28px; font-weight: 700; color: #172554; letter-spacing: -.2px; }
    .dsh-head p { margin: 0; color: var(--muted); font-size: 14px; }
    .dsh-crumb { margin: 0 0 10px; font-size: 13px; color: var(--muted); }
    .dsh-crumb a:hover { text-decoration: underline; }
    .sr-only { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0; }

    .dsh-filter { display: flex; gap: 10px; flex-wrap: wrap; }
    .dsh-filter select { padding: 12px 16px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); color: var(--ink); font: inherit; font-weight: 600; cursor: pointer; }
    .dsh-filter select:focus { outline: 2px solid var(--primary); outline-offset: 0; }

    .dsh-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .dsh-stat { background: var(--surface); border: 1px solid var(--line); border-radius: 10px; padding: 22px 24px; }
    .dsh-stat .label { font-size: 13px; color: var(--muted); }
    .dsh-stat .value { margin: 10px 0 6px; font-size: 34px; font-weight: 700; color: #172554; line-height: 1.1; }
    .dsh-stat .sub { font-size: 13px; color: var(--muted); }

    .dsh-alert { display: flex; gap: 12px; padding: 16px 20px; border-radius: 10px; }
    .dsh-alert svg { width: 20px; height: 20px; flex: 0 0 20px; margin-top: 2px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .dsh-alert strong { display: block; font-size: 15px; }
    .dsh-alert p { margin: 3px 0 0; font-size: 13px; color: #64748b; }
    .dsh-alert.warn { background: #fffbeb; color: #9a5800; }
    .dsh-alert.info { background: #eff6ff; color: #2563eb; }

    .dsh-card { background: var(--surface); border: 1px solid var(--line); border-radius: 10px; padding: 24px; }
    .dsh-card h3 { margin: 0 0 6px; font-size: 18px; font-weight: 700; color: var(--ink); }
    .dsh-card .desc { margin: 0 0 16px; font-size: 13px; color: var(--muted); }
    .dsh-card p.text { margin: 0 0 10px; font-size: 14px; line-height: 1.6; }
    .dsh-card p.hint { margin: 0; font-size: 13px; color: var(--muted); }
    .dsh-card .btn-row { margin-top: 18px; }
    .dsh-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: stretch; }

    .dsh-btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 18px; border-radius: 8px; border: 1px solid var(--line); background: var(--surface); color: var(--ink); font-weight: 600; font-size: 14px; }
    .dsh-btn.primary { background: var(--primary); border-color: var(--primary); color: #fff; }
    .dsh-btn.primary:hover { background: var(--primary-dark); }
    .dsh-link { color: var(--primary); font-weight: 600; }
    .dsh-link:hover { text-decoration: underline; }

    .dsh-table-wrap { border: 1px solid var(--line); border-radius: 10px; overflow-x: auto; background: var(--surface); }
    table.dsh-table { width: 100%; border-collapse: collapse; min-width: 700px; }
    .dsh-table th { text-align: left; font-size: 13px; font-weight: 500; color: var(--muted); padding: 15px 20px; background: #f8fafc; }
    .dsh-table td { padding: 17px 20px; border-top: 1px solid var(--line); font-size: 14px; }
    .dsh-table .strong { font-weight: 600; }
    .dsh-table .empty { text-align: center; color: var(--muted); padding: 30px; }

    .dsh-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #f1f5f9; color: #475569; }
    .dsh-badge.menunggu { background: #fffbeb; color: #9a5800; }
    .dsh-badge.diproses { background: #eff6ff; color: #2563eb; }
    .dsh-badge.disetujui, .dsh-badge.ditanggapi, .dsh-badge.selesai, .dsh-badge.dipublikasikan { background: #f0fdf4; color: #15803d; }
    .dsh-badge.ditolak { background: #fef2f2; color: #dc2626; }

    /* ---------- Grafik ---------- */
    .dsh-chart { margin: 0; }
    .dsh-legend { display: flex; flex-wrap: wrap; gap: 6px 16px; margin: 14px 0 0; padding: 0; list-style: none; font-size: 12px; color: var(--muted); }
    .dsh-legend li { display: inline-flex; align-items: center; gap: 6px; }
    .dsh-legend i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
    .dsh-chart-empty { padding: 36px 0; text-align: center; color: var(--muted); font-size: 13px; }

    .dsh-cols { display: flex; gap: 14px; align-items: flex-end; }
    .dsh-col-group { flex: 1 1 0; min-width: 0; display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .dsh-col-bars { width: 100%; height: 190px; display: flex; align-items: flex-end; justify-content: center; gap: 6px; border-bottom: 1px solid var(--line); }
    .dsh-col { height: 100%; flex: 0 1 36px; min-width: 14px; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; }
    .dsh-col-val { font-size: 12px; font-weight: 600; color: var(--ink); height: 18px; line-height: 18px; }
    .dsh-col-bar { width: 100%; border-radius: 4px 4px 0 0; min-height: 0; }
    .dsh-col-label { font-size: 12px; color: var(--muted); text-align: center; overflow-wrap: anywhere; }

    .dsh-hbars { display: grid; gap: 14px; }
    .dsh-hrow { display: grid; grid-template-columns: minmax(80px, 150px) 1fr auto; gap: 12px; align-items: center; font-size: 13px; }
    .dsh-hrow .name { color: var(--ink); overflow-wrap: anywhere; }
    .dsh-track { height: 14px; border-radius: 7px; background: #eef1f7; overflow: hidden; display: flex; }
    .dsh-track span { display: block; height: 100%; }
    .dsh-hrow .num { min-width: 52px; text-align: right; font-weight: 600; color: var(--ink); }

    .dsh-donut-wrap { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
    .dsh-donut-svg { width: 168px; height: 168px; flex: 0 0 168px; }
    .dsh-donut-total { font-size: 22px; font-weight: 700; fill: #172554; }
    .dsh-donut-cap { font-size: 8px; fill: #64748b; }
    .dsh-donut-legend { margin: 0; padding: 0; list-style: none; display: grid; gap: 10px; font-size: 13px; flex: 1 1 140px; }
    .dsh-donut-legend li { display: flex; align-items: center; gap: 8px; }
    .dsh-donut-legend i { width: 10px; height: 10px; border-radius: 3px; flex: 0 0 10px; }
    .dsh-donut-legend b { margin-left: auto; padding-left: 12px; }

    /* ---------- Detail dosen ---------- */
    .dsh-profile { display: flex; gap: 20px; align-items: flex-start; }
    .dsh-avatar { width: 76px; height: 76px; flex: 0 0 76px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; overflow: hidden; }
    .dsh-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .dsh-profile h3 { margin: 0 0 6px; font-size: 22px; }
    .dsh-profile .meta { margin: 0 0 4px; font-size: 14px; color: var(--muted); }
    .dsh-tags { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0; }
    .dsh-tag { padding: 6px 12px; border-radius: 6px; background: #eff6ff; color: #2563eb; font-size: 13px; font-weight: 600; }
    .dsh-week { display: grid; gap: 6px; margin-bottom: 18px; font-size: 14px; }
    .dsh-week .bar { height: 14px; border-radius: 7px; background: #e2e8f0; overflow: hidden; }
    .dsh-week .bar span { display: block; height: 100%; background: #2563eb; border-radius: 7px; }

    @media (max-width: 960px) {
        .dsh-stats { grid-template-columns: 1fr 1fr; }
        .dsh-grid-2 { grid-template-columns: 1fr; }
    }

    @media (max-width: 560px) {
        .dsh-stats { grid-template-columns: 1fr; }
        .dsh-head h2 { font-size: 24px; }
        .dsh-hrow { grid-template-columns: 1fr auto; }
        .dsh-hrow .track-wrap { grid-column: 1 / -1; order: 3; }
        .dsh-profile { flex-direction: column; }
    }
</style>
