<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgentCommission;
use App\Models\Booking;
use App\Services\Accounting\AgentSettlementService;

/**
 * Commissions are no longer written by hand.
 *
 * Every paid booking that belongs to an agent earns one automatically, at that
 * agent's rate (BookingObserver -> AgentSettlementService). This seeder only
 * makes sure that has happened for the demo bookings and then approves the
 * older half, so the demo opens with some commission already credited to the
 * agent's wallet and some still waiting for the office.
 */
class AgentCommissionSeeder extends Seeder
{
    public function run(): void
    {
        $settlement = app(AgentSettlementService::class);

        // Re-run the rule over the seeded bookings (the observer already did
        // this on save; doing it again is harmless and covers imported rows).
        Booking::whereNotNull('agent_id')->where('status', 'paid')->get()
            ->each(fn (Booking $booking) => $settlement->syncCommission($booking));

        // Approve every other one: approved = credited to the wallet and
        // withdrawable, pending = earned but not yet released.
        AgentCommission::orderBy('id')->get()
            ->each(function (AgentCommission $commission, int $index) use ($settlement) {
                if ($index % 2 === 0) {
                    $settlement->approveCommission($commission);
                }
            });
    }
}
