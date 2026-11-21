{{-- Grafik batang vertikal berkelompok (HTML/CSS). Variabel: $labels, $series [[name,color,values]], $ariaLabel --}}
@php
    $maks = (float) collect($series)->flatMap(fn (array $s): array => $s['values'])->push(1)->max();
@endphp
@if (count($labels) === 0)
    <p class="dsh-chart-empty">Belum ada data untuk ditampilkan.</p>
@else
    <figure class="dsh-chart" role="img" aria-label="{{ $ariaLabel }}">
        <div class="dsh-cols">
            @foreach ($labels as $i => $label)
                <div class="dsh-col-group">
                    <div class="dsh-col-bars">
                        @foreach ($series as $s)
                            @php
                                $nilai = (float) ($s['values'][$i] ?? 0);
                                $rasio = number_format($nilai / $maks, 4, '.', '');
                            @endphp
                            <div class="dsh-col" title="{{ $s['name'] }}: {{ $nilai }}">
                                <span class="dsh-col-val">{{ $nilai }}</span>
                                <span class="dsh-col-bar" style="height: calc((100% - 18px) * {{ $rasio }}); background: {{ $s['color'] }}"></span>
                            </div>
                        @endforeach
                    </div>
                    <span class="dsh-col-label">{{ $label }}</span>
                </div>
            @endforeach
        </div>
        @if (count($series) > 1)
            <ul class="dsh-legend">
                @foreach ($series as $s)
                    <li><i style="background: {{ $s['color'] }}"></i>{{ $s['name'] }}</li>
                @endforeach
            </ul>
        @endif
    </figure>
@endif
