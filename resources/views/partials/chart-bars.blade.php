{{-- Grafik batang horizontal satu seri. Variabel: $labels, $values (angka atau null), $teks (teks nilai per baris), $color, $ariaLabel --}}
@php
    $maks = (float) collect($values)->filter(fn ($v): bool => $v !== null)->push(1)->max();
@endphp
@if (count($labels) === 0)
    <p class="dsh-chart-empty">Belum ada data untuk ditampilkan.</p>
@else
    <figure class="dsh-chart" role="img" aria-label="{{ $ariaLabel }}">
        <div class="dsh-hbars">
            @foreach ($labels as $i => $label)
                @php
                    $nilai = $values[$i] ?? null;
                    $lebar = $nilai === null ? 0 : number_format($nilai / $maks * 100, 2, '.', '');
                @endphp
                <div class="dsh-hrow">
                    <span class="name">{{ $label }}</span>
                    <div class="dsh-track track-wrap"><span style="width: {{ $lebar }}%; background: {{ $color }}"></span></div>
                    <span class="num">{{ $teks[$i] ?? ($nilai ?? '-') }}</span>
                </div>
            @endforeach
        </div>
    </figure>
@endif
