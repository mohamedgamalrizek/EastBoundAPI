<?php

namespace App\Services\Dmc;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Dmc\Opportunity;
use App\Models\Dmc\Quotation;
use App\Models\Dmc\Trip;
use App\Models\Dmc\VendorBill;
use App\Models\Dmc\Website;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ManagementReports
{
    public function sales(array $filters = []): array
    {
        $query = DB::table('dmc_sales_orders')->join('dmc_quotations', 'dmc_quotations.id', '=', 'dmc_sales_orders.quotation_id')
            ->join('dmc_opportunities', 'dmc_opportunities.id', '=', 'dmc_quotations.opportunity_id')
            ->join('users', 'users.id', '=', 'dmc_opportunities.assigned_to')
            ->join('bookings', 'bookings.id', '=', 'dmc_sales_orders.booking_id')
            ->leftJoin('invoices', 'invoices.booking_id', '=', 'bookings.id');
        foreach (['from' => '>=', 'to' => '<='] as $field => $operator) {
            if (! empty($filters[$field])) {
                $query->whereDate('dmc_sales_orders.created_at', $operator, $filters[$field]);
            }
        }
        $rows = $query->groupBy('users.id', 'users.name')->selectRaw('users.name, COUNT(*) as orders,
            SUM(bookings.amount - COALESCE(invoices.refunded_amount, 0)) as sales,
            SUM(COALESCE(invoices.paid_amount, 0) - COALESCE(invoices.refunded_amount, 0)) as collected')
            ->orderByDesc('sales')->get()->map(fn ($row) => [$row->name, (int) $row->orders, (float) $row->sales, (float) $row->collected]);

        return ['title' => 'Sales Team Performance', 'headers' => ['Sales owner', 'Confirmed orders', 'Net booked sales', 'Net collected'],
            'rows' => $rows, 'metrics' => ['Confirmed orders' => $rows->sum(fn ($row) => $row[1]), 'Net booked sales' => $rows->sum(fn ($row) => $row[2])],
            'note' => 'Only approved, customer-confirmed DMC sales orders are included. Dates filter order confirmation. Refunds reduce the reported sales and collection values.'];
    }

    public function receivables(): array
    {
        $invoices = Invoice::with('customer')->whereNotIn('status', ['refunded'])
            ->whereRaw('amount > paid_amount')->orderBy('due_date')->get();
        $rows = $invoices->map(fn ($invoice) => [
            $invoice->invoice_no, $invoice->customer_name, $invoice->due_date?->toDateString(),
            max(0, (int) ($invoice->due_date?->diffInDays(today(), false) ?? 0)),
            round((float) $invoice->amount - (float) $invoice->paid_amount, 2),
        ]);

        return ['title' => 'Receivables & Aging', 'headers' => ['Invoice', 'Customer', 'Due date', 'Days overdue', 'Outstanding'],
            'rows' => $rows, 'metrics' => ['Outstanding receivables' => $rows->sum(fn ($row) => $row[4])]];
    }

    public function profitability(): array
    {
        $trips = Trip::with(['order.quotation', 'order.booking.invoice'])->get();
        $costs = DB::table('dmc_vendor_bills')->join('dmc_vendor_orders', 'dmc_vendor_orders.id', '=', 'dmc_vendor_bills.vendor_order_id')
            ->groupBy('dmc_vendor_orders.trip_id')->selectRaw('dmc_vendor_orders.trip_id, SUM(dmc_vendor_bills.amount) as total')->pluck('total', 'trip_id');
        $commitments = DB::table('dmc_vendor_orders')->groupBy('trip_id')->selectRaw('trip_id, SUM(amount) as total')->pluck('total', 'trip_id');
        $expenses = DB::table('dmc_trip_expenses')->groupBy('trip_id')->selectRaw('trip_id, SUM(amount) as total')->pluck('total', 'trip_id');
        $rows = $trips->map(function ($trip) use ($costs, $commitments, $expenses) {
            $invoice = $trip->order->booking->invoice;
            $revenue = (float) ($invoice?->amount ?? 0) - (float) ($invoice?->refunded_amount ?? 0);
            $cost = (float) ($costs[$trip->id] ?? 0) + (float) ($expenses[$trip->id] ?? 0);
            $committed = (float) ($commitments[$trip->id] ?? 0) + (float) ($expenses[$trip->id] ?? 0);

            return ['Trip #'.$trip->id.' '.$trip->title, $revenue, (float) ($invoice?->paid_amount ?? 0) - (float) ($invoice?->refunded_amount ?? 0),
                (float) $trip->order->quotation->total_cost, $committed, $cost, round($revenue - $cost, 2), round($revenue - max($cost, $committed), 2)];
        });

        return ['title' => 'Trip Profitability', 'headers' => ['Trip', 'Net sales', 'Net collected', 'Quoted cost', 'Committed cost', 'Billed cost', 'Billed margin', 'Committed margin'],
            'rows' => $rows, 'metrics' => ['Net sales' => $rows->sum(fn ($row) => $row[1]), 'Billed margin' => $rows->sum(fn ($row) => $row[6])],
            'note' => 'Margins include posted supplier bills and allocated trip expenses. Committed margin additionally includes unbilled vendor orders. Unallocated company expenses remain in company P&L.'];
    }

    public function cashflow(): array
    {
        $opening = (float) Account::whereIn('cash_type', ['cash', 'bank'])->sum('balance');
        $events = collect();
        Invoice::where('status', '!=', 'refunded')->whereRaw('amount > paid_amount')->get()->each(function ($invoice) use ($events) {
            $events->push(['date' => max(today()->toDateString(), $invoice->due_date?->toDateString() ?? today()->toDateString()),
                'reference' => $invoice->invoice_no, 'inflow' => round((float) $invoice->amount - (float) $invoice->paid_amount, 2), 'outflow' => 0]);
        });
        VendorBill::withSum('payments', 'amount')->get()->each(function ($bill) use ($events) {
            $due = round((float) $bill->amount - (float) $bill->payments_sum_amount, 2);
            if ($due > 0) {
                $events->push(['date' => max(today()->toDateString(), $bill->due_on->toDateString()), 'reference' => $bill->reference,
                    'inflow' => 0, 'outflow' => $due]);
            }
        });
        $running = $opening;
        $rows = $events->sortBy('date')->map(function ($event) use (&$running) {
            $running += $event['inflow'] - $event['outflow'];

            return [$event['date'], $event['reference'], $event['inflow'], $event['outflow'], round($running, 2)];
        })->values();

        return ['title' => 'Contractual Cash Flow Forecast', 'headers' => ['Due date', 'Reference', 'Expected inflow', 'Expected outflow', 'Projected cash'],
            'rows' => $rows, 'metrics' => ['Current cash / bank' => $opening, 'Projected closing cash' => round($running, 2)],
            'note' => 'Projection uses outstanding customer invoices and unpaid DMC vendor bills, by contractual due date. Overdue balances are shown today. This is not a guarantee of collection; legacy vendor ledger balances without bill due dates are excluded.'];
    }

    public function marketing(array $filters = []): array
    {
        $leadQuery = DB::table('leads');
        if (! empty($filters['from'])) {
            $leadQuery->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $leadQuery->whereDate('created_at', '<=', $filters['to']);
        }
        $leads = $leadQuery->get(['id', 'dmc_website_id', 'campaign_id', 'stage']);
        $sales = DB::table('bookings')->joinSub(clone $leadQuery, 'origin', 'origin.id', '=', 'bookings.lead_id')
            ->leftJoin('invoices', 'invoices.booking_id', '=', 'bookings.id')
            ->whereIn('bookings.status', ['confirmed', 'paid'])
            ->selectRaw('origin.dmc_website_id, origin.campaign_id, bookings.id as booking_id, bookings.customer_id, bookings.amount - COALESCE(invoices.refunded_amount, 0) as amount, COALESCE(invoices.paid_amount, 0) - COALESCE(invoices.refunded_amount, 0) as collected')->get();
        $firstPaidBooking = DB::table('receipts')->join('invoices', 'invoices.id', '=', 'receipts.invoice_id')
            ->where('receipts.amount', '>', 0)->whereNotNull('invoices.customer_id')
            ->orderBy('receipts.received_on')->orderBy('receipts.id')->get(['invoices.customer_id', 'invoices.booking_id'])
            ->unique('customer_id')->pluck('booking_id', 'customer_id');
        $spends = DB::table('dmc_campaign_spends');
        if (! empty($filters['from'])) {
            $spends->whereDate('spent_on', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $spends->whereDate('spent_on', '<=', $filters['to']);
        }
        $spends = $spends->get();
        $sites = Website::orderBy('name')->get()->pluck('name', 'id');
        $campaigns = Campaign::pluck('name', 'id');
        $groups = $leads->map(fn ($lead) => [$lead->dmc_website_id, $lead->campaign_id])
            ->merge($spends->map(fn ($spend) => [$spend->website_id, $spend->campaign_id]))
            ->merge($sites->keys()->map(fn ($id) => [$id, null]))->unique(fn ($pair) => json_encode($pair));
        $rows = $groups->map(function ($pair) use ($leads, $sales, $spends, $sites, $campaigns, $firstPaidBooking) {
            [$site, $campaign] = $pair;
            $cohort = $leads->where('dmc_website_id', $site)->where('campaign_id', $campaign);
            $groupSales = $sales->where('dmc_website_id', $site)->where('campaign_id', $campaign);
            $spend = (float) $spends->where('website_id', $site)->where('campaign_id', $campaign)->sum('amount');
            $revenue = (float) $groupSales->sum('amount');
            $customers = $groupSales->filter(fn ($sale) => $sale->collected > 0 && $sale->customer_id
                && ($firstPaidBooking[$sale->customer_id] ?? null) == $sale->booking_id)->pluck('customer_id')->unique()->count();
            $won = $cohort->where('stage', 'Won')->count();

            return [$sites[$site] ?? 'Unattributed', $campaign ? '#'.$campaign.' '.($campaigns[$campaign] ?? 'Unknown campaign') : 'No campaign', $cohort->count(), $won,
                $cohort->count() ? round($won / $cohort->count() * 100, 2) : 0, $revenue, (float) $groupSales->sum('collected'),
                $spend, $spend > 0 ? round($revenue / $spend, 2) : null, $customers, $customers > 0 ? round($spend / $customers, 2) : null];
        })->values();

        $summaries = [];
        foreach ([0 => 'Website Totals', 1 => 'Campaign Totals'] as $dimension => $title) {
            $summary = $rows->groupBy(fn ($row) => $row[$dimension])->map(function ($group, $name) {
                $leads = $group->sum(fn ($row) => $row[2]);
                $won = $group->sum(fn ($row) => $row[3]);
                $sales = $group->sum(fn ($row) => $row[5]);
                $spend = $group->sum(fn ($row) => $row[7]);
                $customers = $group->sum(fn ($row) => $row[9]);

                return [$name, $leads, $won, $leads ? round($won / $leads * 100, 2) : 0,
                    $sales, $group->sum(fn ($row) => $row[6]), $spend, $spend > 0 ? round($sales / $spend, 2) : null,
                    $customers, $customers > 0 ? round($spend / $customers, 2) : null];
            })->sortByDesc(fn ($row) => $row[4])->values();
            $summaries[] = ['title' => $title, 'headers' => ['Source', 'Leads', 'Won', 'Conversion %', 'Net sales', 'Net collected', 'Actual spend', 'ROAS', 'New customers', 'CAC'], 'rows' => $summary];
        }

        return ['title' => 'Website & Campaign Performance — ROAS / CAC',
            'headers' => ['Website', 'Campaign', 'Leads', 'Won', 'Conversion %', 'Net booked sales', 'Net collected', 'Actual spend', 'ROAS', 'New paying customers', 'CAC'],
            'rows' => $rows, 'tables' => $summaries, 'metrics' => ['Leads' => $leads->count(), 'Actual spend' => $spends->sum('amount')],
            'note' => 'Lead-cohort report: dates filter lead creation and spend dates. Sales/collections show all linked package/DMC orders for that cohort. ROAS = net booked sales / actual spend. CAC = spend / customers whose first recorded payment belongs to this website/campaign. Undefined ratios are shown as —. Legacy unlinked attribution is reported separately.'];
    }

    public function overview(): array
    {
        return ['Pending quotation approvals' => Quotation::where('status', 'Submitted')->count(),
            'Upcoming trips' => Trip::whereIn('status', ['Planning', 'Ready'])->whereDate('starts_on', '>=', today())->count(),
            'Trips operating today' => Trip::whereDate('starts_on', '<=', today())->whereDate('ends_on', '>=', today())->where('status', '!=', 'Cancelled')->count(),
            'Open purchase requests' => DB::table('dmc_purchase_requests')->whereIn('status', ['Requested', 'RFQ'])->count()];
    }

    public function departmentOverview(User $user): array
    {
        $metrics = [];
        if ($user->can_access('crm_read')) {
            $metrics['My active leads'] = DB::table('leads')->where('assigned_to', $user->id)->whereNotIn('stage', ['Won', 'Lost'])->count();
        }
        if ($user->can_access('task_read')) {
            $metrics['My open tasks'] = DB::table('tasks')->where('assigned_to', $user->id)->where('status', '!=', 'Done')->count();
        }
        if ($user->can_access('dmc_sales_read')) {
            $metrics['My open opportunities'] = Opportunity::where('assigned_to', $user->id)->whereNotIn('stage', ['Won', 'Lost'])->count();
            $metrics['My draft quotations'] = Quotation::where('created_by', $user->id)->where('status', 'Draft')->count();
            $query = Quotation::where('status', 'Submitted');
            if (! $user->can_access('dmc_sales_approve_tl')) {
                $query->where('created_by', $user->id);
            }
            $metrics['Quotations awaiting approval'] = $query->count();
        }
        if ($user->can_access('dmc_operations_update')) {
            $metrics['My active trips'] = Trip::where('manager_id', $user->id)->whereIn('status', ['Planning', 'Ready', 'In Progress'])->count();
            $metrics['Open purchase requests'] = DB::table('dmc_purchase_requests')->whereIn('status', ['Requested', 'RFQ'])->count();
        }
        if ($user->can_access('accounting_read')) {
            $metrics['Outstanding customer invoices'] = Invoice::whereRaw('amount > paid_amount')->where('status', '!=', 'refunded')->count();
            $metrics['DMC vendor bills'] = VendorBill::count();
        }
        if ($user->can_access('dmc_marketing_read')) {
            $metrics['Website leads this month'] = DB::table('leads')->whereNotNull('dmc_website_id')->where('created_at', '>=', today()->startOfMonth())->count();
            $metrics['Recorded marketing spend this month'] = DB::table('dmc_campaign_spends')->whereDate('spent_on', '>=', today()->startOfMonth())->sum('amount');
        }

        return $metrics;
    }
}
