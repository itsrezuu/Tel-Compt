@extends('layouts.admin-hima')
@section('title', 'Verifikasi & Arsip')
@section('page_title', 'Verifikasi & Arsip')
@push('styles')
<style>
.vh-head h2{margin:0 0 6px;font-size:27px}.vh-head p{margin:0 0 22px;color:var(--muted)}.vh-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px}.vh-tabs a{padding:11px 13px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--muted)}.vh-tabs a.active{border-color:var(--primary);color:var(--primary);font-weight:600}.vh-search{display:grid;gap:8px;margin-bottom:24px}.vh-search input{padding:13px;border:1px solid var(--line);border-radius:7px;font:inherit}.vh-table{overflow:auto;border:1px solid var(--line);border-radius:11px;background:#fff}.vh-table table{width:100%;border-collapse:collapse;min-width:740px}.vh-table th{text-align:left;padding:14px 18px;background:#fbfcfe;color:var(--muted);font-size:12px}.vh-table td{padding:17px 18px;border-top:1px solid var(--line)}.vh-table a{color:var(--primary);font-weight:600}.vh-badge{padding:4px 9px;border-radius:6px;background:#fff6dd;color:#a15c00;font-size:12px;font-weight:600}.vh-badge.done{background:#e8f7ee;color:#14803f}.vh-badge.rejected{background:#fdecec;color:#b42318}.vh-foot{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:20px;color:var(--muted);font-size:13px}
</style>
@endpush
@section('content')
@php($labels=['menunggu_verifikasi'=>'Menunggu Verifikasi','arsip_menunggu'=>'Arsip menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak'])
<div class="vh-head"><h2>Verifikasi prestasi &amp; moderasi arsip</h2><p>Periksa bukti prestasi, keluaran lomba dan kelayakan pengalaman untuk publikasi.</p></div>
<nav class="vh-tabs" aria-label="Filter status">@foreach($labels as $key=>$label)<a href="{{ route('admin.verifikasi.index',['status'=>$key]) }}" class="{{ $tab===$key?'active':'' }}" @if($tab===$key) aria-current="page" @endif>{{ $label }}@if(isset($counts[$key])) ({{ $counts[$key] }})@endif</a>@endforeach</nav>
<form class="vh-search" method="GET" action="{{ route('admin.verifikasi.index') }}"><input type="hidden" name="status" value="{{ $tab }}"><label for="q">Cari pengajuan</label><input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="Nomor, mahasiswa atau nama lomba"></form>
<div class="vh-table"><table><thead><tr><th>Prestasi / arsip</th><th>Mahasiswa</th><th>Berkas</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($pengajuans as $p)<tr><td><strong>{{ $p->nomor_pengajuan }} · {{ $p->lomba }}</strong><br>{{ $p->judul_arsip }}</td><td>{{ $p->mahasiswa?->name ?? '-' }}</td><td>{{ $p->dokumen_pengalaman_path?'3 berkas lengkap':'2 berkas' }}</td><td><span class="vh-badge {{ $p->status==='ditolak'?'rejected':($p->status==='disetujui'?'done':'') }}">{{ $p->status==='menunggu_verifikasi'?'Menunggu Verifikasi':($p->status_arsip==='menunggu'?'Arsip menunggu':$p->status_label) }}</span></td><td><a href="{{ route('admin.verifikasi.show',$p) }}">Tinjau bukti &amp; arsip</a></td></tr>@empty<tr><td colspan="5" style="text-align:center;padding:36px;color:var(--muted)">Belum ada pengajuan pada status ini.</td></tr>@endforelse
</tbody></table></div><div class="vh-foot"><span>Menampilkan {{ $pengajuans->count() }} dari {{ $pengajuans->total() }} data</span>@include('partials.pager',['paginator'=>$pengajuans])</div>
@endsection
