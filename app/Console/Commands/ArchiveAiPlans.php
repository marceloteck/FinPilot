<?php

namespace App\Console\Commands;

use App\Models\AiPlan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveAiPlans extends Command
{
    protected $signature = 'ai:archive-plans {--days=90 : Dias para arquivar planos confirmados} {--draft-days=30 : Dias para arquivar drafts}';

    protected $description = 'Arquiva planos de IA antigos para manter a base enxuta.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $draftDays = (int) $this->option('draft-days');

        $confirmedThreshold = Carbon::now()->subDays($days);
        $draftThreshold = Carbon::now()->subDays($draftDays);

        $archivedConfirmed = AiPlan::query()
            ->where('status', 'confirmed')
            ->where('created_at', '<', $confirmedThreshold)
            ->update(['status' => 'archived']);

        $archivedDrafts = AiPlan::query()
            ->where('status', 'draft')
            ->where('created_at', '<', $draftThreshold)
            ->update(['status' => 'archived']);

        Log::info('AI plans archived', [
            'archived_confirmed' => $archivedConfirmed,
            'archived_drafts' => $archivedDrafts,
        ]);

        $this->info("Planos arquivados: confirmados={$archivedConfirmed}, drafts={$archivedDrafts}");

        return self::SUCCESS;
    }
}
