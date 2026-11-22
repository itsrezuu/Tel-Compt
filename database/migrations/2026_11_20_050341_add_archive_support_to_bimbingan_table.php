<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $archivedIds = $this->duplicateHistoryIds();

        Schema::table('bimbingan', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('active_lomba_id')
                ->nullable()
                ->virtualAs('CASE WHEN archived_at IS NULL THEN lomba_id ELSE NULL END');
        });

        if ($archivedIds !== []) {
            DB::table('bimbingan')
                ->whereIn('bimbingan_id', $archivedIds)
                ->update(['archived_at' => now()]);
        }

        Schema::table('bimbingan', function (Blueprint $table): void {
            $table->unique(
                ['mahasiswa_id', 'active_lomba_id'],
                'bimbingan_mahasiswa_active_lomba_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('bimbingan', function (Blueprint $table): void {
            $table->dropUnique('bimbingan_mahasiswa_active_lomba_unique');
            $table->dropColumn('active_lomba_id');
            $table->dropColumn('archived_at');
        });
    }

    /**
     * Keep a single current row for each student and competition while retaining
     * duplicate rows, including their consultation logs, as archived history.
     *
     * @return list<int>
     */
    private function duplicateHistoryIds(): array
    {
        $groups = DB::table('bimbingan')
            ->select('mahasiswa_id', 'lomba_id')
            ->whereNotNull('lomba_id')
            ->groupBy('mahasiswa_id', 'lomba_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $archivedIds = [];

        foreach ($groups as $group) {
            $requests = DB::table('bimbingan')
                ->where('mahasiswa_id', $group->mahasiswa_id)
                ->where('lomba_id', $group->lomba_id)
                ->orderByDesc('created_at')
                ->orderByDesc('bimbingan_id')
                ->get(['bimbingan_id', 'status']);

            $active = $requests->filter(
                fn (object $request): bool => in_array($request->status, ['menunggu', 'disetujui'], true),
            );

            if ($active->count() > 1) {
                throw new LogicException(
                    "Pasangan mahasiswa-lomba {$group->mahasiswa_id}-{$group->lomba_id} memiliki lebih dari satu pengajuan aktif.",
                );
            }

            $current = $active->first() ?? $requests->first();

            foreach ($requests as $request) {
                if ((int) $request->bimbingan_id !== (int) $current->bimbingan_id) {
                    $archivedIds[] = (int) $request->bimbingan_id;
                }
            }
        }

        return $archivedIds;
    }
};
