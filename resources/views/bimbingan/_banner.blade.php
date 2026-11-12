{{-- Banner keputusan bimbingan. Variabel: $status, $auto (bool), $untuk ('dosen' | 'mahasiswa') --}}
@if ($status === 'disetujui')
    <div class="bm-banner ok">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
        <div>
            <strong>Bimbingan disetujui</strong>
            <p>
                {{ $untuk === 'dosen'
                    ? 'Keputusan telah tercatat. Mahasiswa menerima notifikasi dan dapat melihat arahan pada riwayat.'
                    : 'Keputusan dosen telah tercatat. Kamu sekarang bisa mengirim konsultasi ke pembimbing.' }}
            </p>
        </div>
    </div>
@elseif ($status === 'ditolak')
    <div class="bm-banner bad">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
        <div>
            <strong>{{ $auto ? 'Bimbingan ditolak otomatis' : 'Bimbingan ditolak' }}</strong>
            <p>
                @if ($auto)
                    Dosen tidak merespons dalam 1 hari, jadi pengajuan ditolak oleh sistem.
                @elseif ($untuk === 'dosen')
                    Keputusan telah tercatat. Mahasiswa menerima notifikasi dan dapat melihat arahan pada riwayat.
                @else
                    Keputusan dosen telah tercatat. Kamu bisa mengajukan bimbingan lagi ke dosen yang sama atau yang lain.
                @endif
            </p>
        </div>
    </div>
@endif
