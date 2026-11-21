{{-- Filter periode + dosen. Variabel: $rute (nama route), $opsiPeriode, $periodeKey, $semuaDosen, $dosenId --}}
@use('Illuminate\Support\Str')
<form method="GET" action="{{ route($rute) }}" class="dsh-filter">
    <label class="sr-only" for="periode">Periode</label>
    <select id="periode" name="periode" onchange="this.form.submit()">
        @foreach ($opsiPeriode as $opsi)
            <option value="{{ $opsi['key'] }}" @selected($opsi['key'] === $periodeKey)>{{ $opsi['label'] }}</option>
        @endforeach
    </select>

    <label class="sr-only" for="dosen">Dosen</label>
    <select id="dosen" name="dosen" onchange="this.form.submit()">
        <option value="">Semua dosen</option>
        @foreach ($semuaDosen as $d)
            <option value="{{ $d->id }}" @selected((int) $dosenId === (int) $d->id)>{{ trim(Str::before($d->name, ',')) }}</option>
        @endforeach
    </select>

    <noscript><button type="submit" class="dsh-btn">Terapkan</button></noscript>
</form>
