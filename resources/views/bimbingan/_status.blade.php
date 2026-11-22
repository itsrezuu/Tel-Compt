@php
    $labelStatus = ['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditanggapi' => 'Disetujui', 'ditolak' => 'Ditolak', 'selesai' => 'Selesai'];
@endphp
<span class="bm-badge {{ $status }}">{{ $labelStatus[$status] ?? ucfirst($status) }}</span>
