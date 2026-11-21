{{-- Grafik batang horizontal bertumpuk. Variabel: $labels, $series [[name,color,values]], $ariaLabel --}}
@php
    $jumlah = collect($labels)->map(fn ($l, int $i): float => (float) collect($series)->sum(fn (array $s) => $s['values'][$i] ?? 0));
    $maks = (float) $jumlah->push(1)->max();
@endphp
@if (count($labels) === 0)
    <p class="dsh-chart-empty">Belum ada data untuk ditampilkan.</p>
@else
    <figure class="dsh-chart" role="img" aria-label="{{ $ariaLabel }}">
        <div class="dsh-hbars">
            @foreach ($labels as $i => $label)
                <div class="dsh-hrow">
                    <span class="name">{{ $label }}</span>
                    <div class="dsh-track track-wrap">
                        @foreach ($series as $s)
                            @php $nilai = (float) ($s['values'][$i] ?? 0); @endphp
                            @if ($nilai > 0)
                                <span style="width: {{ number_format($nilai / $maks * 100, 2, '.', '') }}%; background: {{ $s['color'] }}" title="{{ $s['name'] }}: {{ $nilai }}"></span>
                            @endif
                        @endforeach
                    </div>
                    <span class="num">{{ $jumlah[$i] }}</span>
                </div>
            @endforeach
        </div>
        <ul class="dsh-legend">
            @foreach ($series as $s)
                <li><i style="background: {{ $s['color'] }}"></i>{{ $s['name'] }}</li>
            @endforeach
        </ul>
    </figure>
@endif
