<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgentWithdrawal;
use App\Models\Role;
use App\Models\User;
use App\Services\Accounting\AgentSettlementService;

/**
 * Demo payouts, one at each stage of the flow, so every state on the Payouts
 * screen has something in it: one already paid (wallet debited, cash posted),
 * one approved and waiting to be sent, one just requested.
 *
 * Amounts are sized against the agent's actual wallet balance — a demo payout
 * larger than the wallet would be exactly the inconsistency this work removed.
 */
class AgentWithdrawalSeeder extends Seeder
{
    public function run(): void
    {
        $settlement = app(AgentSettlementService::class);

        $agents = User::where('role_id', Role::where('name', 'Agent')->value('id'))->get();

        foreach ($agents as $agent) {
            $balance = $settlement->walletBalance($agent->id);

            if ($balance < 1000) {
                continue; // nothing credited yet: nothing to withdraw
            }

            // Keep well inside the balance: paid + open requests together stay
            // under what the wallet holds.
            $paid      = round($balance * 0.30, 2);
            $approved  = round($balance * 0.20, 2);
            $requested = round($balance * 0.15, 2);

            // Dated after the commissions were credited: a payout cannot come
            // out of a wallet before the money went in, and the running
            // balance would show it if it did.
            $rows = [
                ['paid',      $paid,      'Bank',  'City Bank ****4421', '-3 days', 'today'],
                ['approved',  $approved,  'bKash', '01700000007',        '-2 days', null],
                ['requested', $requested, 'Bank',  'City Bank ****4421', '-1 day',  null],
            ];

            foreach ($rows as $i => $r) {
                AgentWithdrawal::updateOrCreate(
                    ['reference' => 'WDL-' . $agent->id . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT)],
                    [
                        'agent_id'        => $agent->id,
                        'amount'          => $r[1],
                        'method'          => $r[2],
                        'account_details' => $r[3],
                        'status'          => $r[0],
                        'requested_on'    => now()->modify($r[4])->toDateString(),
                        'processed_on'    => $r[5] ? now()->modify($r[5])->toDateString() : null,
                        'note'            => $r[0] === 'paid' ? 'Transferred to bank.' : null,
                    ]
                );
            }
        }
    }
}
