<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgentWalletTransaction;
use App\Services\Accounting\AgentSettlementService;

/**
 * The agent wallet is no longer seeded with invented figures.
 *
 * Every line now comes from something real — an approved commission credits
 * it, a paid withdrawal debits it — so this seeder only clears out any
 * hand-written leftovers and recomputes the running balances from the lines
 * that have a source.
 */
class AgentWalletTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Legacy rows from before wallet lines carried their source: they
        // belong to no commission or payout, so nothing can verify them.
        AgentWalletTransaction::whereNull('source_type')->delete();

        app(AgentSettlementService::class)->rebuildAllWallets();
    }
}
