<?php

namespace App\Console\Commands;

use App\Models\Bimbingan;
use Illuminate\Console\Command;

class TolakBimbinganKadaluarsa extends Command
{
    protected $signature = 'bimbingan:tolak-kadaluarsa';

    protected $description = 'Tolak otomatis pengajuan bimbingan yang tidak direspons dosen dalam 1 hari';

    public function handle(): int
    {
        $jumlah = Bimbingan::tolakKadaluarsa();

        $this->info("{$jumlah} pengajuan bimbingan ditolak otomatis.");

        return self::SUCCESS;
    }
}
