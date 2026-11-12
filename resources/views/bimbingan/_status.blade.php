@php
    $labelStatus = ['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditanggapi' => 'Ditanggapi', 'ditolak' => 'Ditolak'];
@endphp
<span class="bm-badge {{ $status }}">{{ $labelStatus[$status] ?? ucfirst($status) }}</span>
