@push('styles')
<style>
    .prestasi-page-head h2 { color: var(--ink); font-size: 28px; font-weight: 700; letter-spacing: -.2px; margin: 0 0 6px; }
    .prestasi-page-head p { color: var(--muted); font-size: 14px; margin: 0; }
    .prestasi-card, .prestasi-record { background: var(--surface); border: 1px solid var(--line); border-radius: 12px; margin-top: 24px; padding: 24px; }
    .prestasi-card h3, .prestasi-record h3 { font-size: 18px; font-weight: 600; margin: 0 0 20px; }
    .prestasi-grid { display: grid; gap: 0 16px; grid-template-columns: 1fr 1fr; }
    .prestasi-field { margin-bottom: 20px; }
    .prestasi-field label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; }
    .prestasi-field input[type="text"], .prestasi-field input[type="date"], .prestasi-field input[type="file"], .prestasi-field textarea {
        background: var(--surface); border: 1px solid var(--line); border-radius: 8px; color: var(--ink); font: inherit; padding: 13px 14px; width: 100%;
    }
    .prestasi-field input[type="file"] { border-style: dashed; }
    .prestasi-field input[type="file"]::file-selector-button {
        background: #eff4ff; border: 0; border-radius: 6px; color: var(--primary); cursor: pointer; font: inherit; font-weight: 600; margin-right: 12px; padding: 8px 10px;
    }
    .prestasi-field textarea { line-height: 1.5; min-height: 96px; resize: vertical; }
    .prestasi-field input:focus, .prestasi-field textarea:focus { border-color: var(--primary); outline: 2px solid var(--primary); outline-offset: 0; }
    .prestasi-field.has-error input, .prestasi-field.has-error textarea { border-color: #dc2626; }
    .prestasi-field .error-text { color: #b42318; font-size: 12px; margin-top: 6px; }
    .prestasi-field .hint { color: var(--muted); font-size: 12px; margin-top: 6px; }
    .prestasi-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 10px; margin-top: 4px; }
    .prestasi-btn { align-items: center; border: 1px solid transparent; border-radius: 8px; display: inline-flex; font-size: 14px; font-weight: 600; justify-content: center; padding: 12px 16px; }
    .prestasi-btn.primary { background: var(--primary); color: #fff; }
    .prestasi-btn.primary:hover { background: var(--primary-dark); }
    .prestasi-btn.ghost { background: var(--surface); border-color: var(--line); color: var(--ink); }
    .prestasi-btn.ghost:hover { border-color: var(--primary); }
    .prestasi-consent { align-items: flex-start; color: var(--muted); display: flex; font-size: 13px; gap: 8px; margin: 0 0 18px; }
    .prestasi-consent input { margin-top: 3px; }
    .prestasi-banner { align-items: flex-start; background: #effaf3; border-radius: 8px; color: #14803f; display: flex; gap: 12px; margin-bottom: 16px; padding: 16px 20px; }
    .prestasi-banner .title { font-size: 14px; font-weight: 600; }
    .prestasi-banner .desc { color: var(--muted); font-size: 13px; margin-top: 2px; }
    .prestasi-badge { background: #fff6dd; border-radius: 6px; color: #a15c00; display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 9px; }
    .prestasi-badge.waiting { background: #fff6dd; color: #a15c00; }
    .prestasi-badge.success { background: #e8f7ee; color: #14803f; }
    .prestasi-badge.rejected { background: #fdecec; color: #b42318; }
    .prestasi-links { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 18px; }
    .prestasi-link { color: var(--primary); font-size: 13px; font-weight: 600; }
    .prestasi-link:hover { text-decoration: underline; }
    .prestasi-record-header { align-items: flex-start; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; }
    .prestasi-record-header h3 { margin-bottom: 6px; }
    .prestasi-record-header p { color: var(--muted); font-size: 13px; margin: 0; }
    .prestasi-detail { display: grid; gap: 12px 20px; grid-template-columns: 180px 1fr; margin: 18px 0 0; }
    .prestasi-detail dt { color: var(--muted); }
    .prestasi-detail dd { margin: 0; white-space: pre-line; }
    .prestasi-empty { background: var(--surface); border: 1px solid var(--line); border-radius: 10px; color: var(--muted); margin-top: 24px; padding: 36px 20px; text-align: center; }
    @media (max-width: 700px) {
        .prestasi-page-head h2 { font-size: 24px; }
        .prestasi-card, .prestasi-record { padding: 18px; }
        .prestasi-grid { grid-template-columns: 1fr; }
        .prestasi-detail { gap: 4px; grid-template-columns: 1fr; }
        .prestasi-detail dd { margin-bottom: 10px; }
    }
</style>
@endpush