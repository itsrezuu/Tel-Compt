{{-- Grafik donat (SVG). Variabel: $items [[label,value,color]], $ariaLabel, $pusat (teks kecil di tengah) --}}
@php
    $total = (int) collect($items)->sum('value');
    $keliling = 2 * M_PI * 45;
    $geser = 0.0;
@endphp
<figure class="dsh-chart dsh-donut-wrap" role="img" aria-label="{{ $ariaLabel }}">
    <svg viewBox="0 0 120 120" class="dsh-donut-svg" aria-hidden="true">
        <circle cx="60" cy="60" r="45" fill="none" stroke="#eef1f7" stroke-width="16"/>
        @if ($total > 0)
            @foreach ($items as $item)
                @continue($item['value'] <= 0)
                @php $panjang = $item['value'] / $total * $keliling; @endphp
                <circle cx="60" cy="60" r="45" fill="none" stroke="{{ $item['color'] }}" stroke-width="16"
                        stroke-dasharray="{{ round($panjang, 3) }} {{ round($keliling - $panjang, 3) }}"
                        stroke-dashoffset="{{ round(-$geser, 3) }}" transform="rotate(-90 60 60)"/>
                @php $geser += $panjang; @endphp
            @endforeach
        @endif
        <text x="60" y="61" text-anchor="middle" class="dsh-donut-total">{{ $total }}</text>
        <text x="60" y="73" text-anchor="middle" class="dsh-donut-cap">{{ $pusat ?? 'Total' }}</text>
    </svg>
    <ul class="dsh-donut-legend">
        @foreach ($items as $item)
            <li>
                <i style="background: {{ $item['color'] }}"></i>{{ $item['label'] }}
                <b>{{ $item['value'] }}</b>
            </li>
        @endforeach
    </ul>
</figure>
