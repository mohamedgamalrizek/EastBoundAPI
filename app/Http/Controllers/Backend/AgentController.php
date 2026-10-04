<?php

namespace App\Http\Controllers\Backend;

use App\Models\AgentCommission;
use App\Models\AgentInvoice;
use App\Models\AgentWalletTransaction;
use App\Models\Booking;
use App\Models\Role;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Back-office list of B2B agents. Agents are Users carrying the "Agent" role —
 * they are created through Users & Roles; this screen is the read-only roster
 * with each agent's production (bookings, sales, commission, wallet).
 */
class AgentController extends Controller
{
    public function index(Request $request)
    {
        $roleId = Role::where('name', 'Agent')->value('id');

        $agents = User::where('role_id', $roleId)
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")))
            ->latest()
            ->get();

        $ids = $agents->pluck('id');

        // One grouped query per metric instead of N per agent.
        $bookingStats = Booking::whereIn('agent_id', $ids)
            ->selectRaw('agent_id, COUNT(*) as bookings, COALESCE(SUM(amount), 0) as sales')
            ->groupBy('agent_id')
            ->get()
            ->keyBy('agent_id');

        $commissionStats = AgentCommission::whereIn('agent_id', $ids)
            ->selectRaw('agent_id, COALESCE(SUM(amount), 0) as earned,
                         COALESCE(SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END), 0) as pending')
            ->groupBy('agent_id')
            ->get()
            ->keyBy('agent_id');

        $unpaidInvoices = AgentInvoice::whereIn('agent_id', $ids)
            ->where('status', '!=', 'paid')
            ->selectRaw('agent_id, COUNT(*) as unpaid')
            ->groupBy('agent_id')
            ->pluck('unpaid', 'agent_id');

        // Wallet balance = balance_after on each agent's newest transaction.
        $wallets = AgentWalletTransaction::whereIn('agent_id', $ids)
            ->orderByDesc('id')
            ->get()
            ->groupBy('agent_id')
            ->map(fn ($rows) => (float) $rows->first()->balance_after);

        $agents->each(function (User $a) use ($bookingStats, $commissionStats, $unpaidInvoices, $wallets) {
            $a->stat_bookings   = (int) ($bookingStats[$a->id]->bookings ?? 0);
            $a->stat_sales      = (float) ($bookingStats[$a->id]->sales ?? 0);
            $a->stat_commission = (float) ($commissionStats[$a->id]->earned ?? 0);
            $a->stat_pending    = (float) ($commissionStats[$a->id]->pending ?? 0);
            $a->stat_unpaid_inv = (int) ($unpaidInvoices[$a->id] ?? 0);
            $a->stat_wallet     = (float) ($wallets[$a->id] ?? 0);
        });

        return view('backend.agent.index', [
            'agents'    => $agents,
            'search'    => $request->search,
            'analytics' => [
                'stats' => [
                    'Total Agents'     => number_format($agents->count()),
                    'Total Sales'      => currency_symbol() . number_format($agents->sum('stat_sales')),
                    'Total Commission' => currency_symbol() . number_format($agents->sum('stat_commission')),
                    'Wallet Float'     => currency_symbol() . number_format($agents->sum('stat_wallet')),
                ],
            ],
        ]);
    }
}
