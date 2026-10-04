<?php

namespace App\Repositories\Report;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\AgentCommission;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\FlightBooking;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\VisaApplication;
use App\Repositories\Report\ReportInterface;

class ReportRepository implements ReportInterface
{
    /**
     * Apply an optional [from, to] date window to a query on the given column.
     * Empty filters leave the query untouched (whole-table report).
     */
    protected function between($query, string $col, array $filters)
    {
        if (!empty($filters['from'])) {
            $query->whereDate($col, '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate($col, '<=', $filters['to']);
        }

        return $query;
    }

    public function sales(array $filters = [])
    {
        $base       = fn () => $this->between(Booking::query(), 'created_at', $filters);
        $totalSales = (float) $base()->sum('amount');
        $orders     = $base()->count();

        return [
            'totalSales'  => $totalSales,
            'orders'      => $orders,
            'avgOrder'    => $orders ? $totalSales / $orders : 0,
            'byStatus'    => $base()->selectRaw('status, count(*) as orders, sum(amount) as revenue')
                ->groupBy('status')->orderByDesc('revenue')->get(),
            'topPackages' => $base()->selectRaw('package_id, count(*) as orders, sum(amount) as revenue')
                ->with('package')
                ->groupBy('package_id')->orderByDesc('revenue')->take(10)->get(),
        ];
    }

    public function visa(array $filters = [])
    {
        $base = fn () => $this->between(VisaApplication::query(), 'applied_date', $filters);

        return [
            'total'      => $base()->count(),
            'approved'   => $base()->where('status', 'Approved')->count(),
            'processing' => $base()->whereIn('status', ['Processing', 'In Review'])->count(),
            'rejected'   => $base()->where('status', 'Rejected')->count(),
            'byStatus'   => $base()->selectRaw('status, count(*) as total')
                ->groupBy('status')->orderByDesc('total')->get(),
            'byCountry'  => $base()->selectRaw('country, count(*) as total')
                ->groupBy('country')->orderByDesc('total')->get(),
            'byType'     => $base()->selectRaw('visa_type, count(*) as total')
                ->groupBy('visa_type')->orderByDesc('total')->get(),
        ];
    }

    public function package(array $filters = [])
    {
        $bookingAgg = $this->between(Booking::query(), 'created_at', $filters)
            ->selectRaw('package_id, count(*) as orders, sum(amount) as revenue')
            ->groupBy('package_id')->get()->keyBy('package_id');

        $byPackage = Package::orderBy('title')->get()->map(function ($p) use ($bookingAgg) {
            $agg = $bookingAgg->get($p->id);
            return (object) [
                'title'       => $p->title,
                'destination' => $p->destination,
                'category'    => $p->category,
                'price'       => (float) $p->price,
                'orders'      => $agg ? (int) $agg->orders : 0,
                'revenue'     => $agg ? (float) $agg->revenue : 0.0,
            ];
        });

        return [
            'totalPackages' => Package::count(),
            'totalBookings' => $this->between(Booking::query(), 'created_at', $filters)->count(),
            'totalRevenue'  => (float) $this->between(Booking::query(), 'created_at', $filters)->sum('amount'),
            'destinations'  => Package::distinct('destination')->count('destination'),
            'byPackage'     => $byPackage,
            'byDestination' => $this->packageGroup($byPackage, 'destination'),
            'byCategory'    => $this->packageGroup($byPackage, 'category'),
        ];
    }

    // Collapse package rows into destination/category buckets.
    private function packageGroup($byPackage, string $key)
    {
        return $byPackage->groupBy($key)->map(function ($rows, $name) {
            return (object) [
                'name'     => $name,
                'packages' => $rows->count(),
                'orders'   => $rows->sum('orders'),
                'revenue'  => $rows->sum('revenue'),
            ];
        })->sortByDesc('revenue')->values();
    }

    public function flight(array $filters = [])
    {
        $base = fn () => $this->between(FlightBooking::query(), 'created_at', $filters);

        return [
            'total'     => $base()->count(),
            'totalFare' => (float) $base()->sum('fare'),
            'confirmed' => $base()->where('status', 'Confirmed')->count(),
            'refunded'  => $base()->whereIn('status', ['Refunded', 'Cancelled'])->count(),
            'byStatus'  => $base()->selectRaw('status, count(*) as total, sum(fare) as fare')
                ->groupBy('status')->orderByDesc('total')->get(),
            'byAirline' => $base()->selectRaw('airline, count(*) as total, sum(fare) as fare')
                ->groupBy('airline')->orderByDesc('fare')->get(),
        ];
    }

    public function hotel(array $filters = [])
    {
        $base = fn () => $this->between(Hotel::query(), 'created_at', $filters);

        return [
            'total'      => $base()->count(),
            'rooms'      => (int) $base()->sum('rooms_count'),
            'cities'     => $base()->distinct('city')->count('city'),
            'avgPrice'   => (float) $base()->avg('price_per_night'),
            'byCity'     => $base()->selectRaw('city, country, count(*) as hotels, sum(rooms_count) as rooms, avg(price_per_night) as avg_price')
                ->groupBy('city', 'country')->orderByDesc('hotels')->get(),
            'byCategory' => $base()->selectRaw('category, count(*) as hotels, sum(rooms_count) as rooms, avg(price_per_night) as avg_price')
                ->groupBy('category')->orderByDesc('category')->get(),
        ];
    }

    public function agent(array $filters = [])
    {
        $base    = fn () => $this->between(AgentCommission::query(), 'earned_on', $filters);
        $total   = (float) $base()->sum('amount');
        $paid    = (float) $base()->where('status', 'paid')->sum('amount');
        $pending = (float) $base()->where('status', 'pending')->sum('amount');

        return [
            'totalCommission' => $total,
            'paid'            => $paid,
            'pending'         => $pending,
            'records'         => $base()->count(),
            'byStatus'        => $base()->selectRaw('status, count(*) as records, sum(amount) as amount')
                ->groupBy('status')->orderByDesc('amount')->get(),
            'recent'          => $base()->latest('earned_on')->take(10)->get(),
        ];
    }

    public function customer(array $filters = [])
    {
        $base = fn () => $this->between(Customer::query(), 'created_at', $filters);

        return [
            'total'    => $base()->count(),
            'active'   => $base()->where('status', 'active')->count(),
            'inactive' => $base()->where('status', '!=', 'active')->count(),
            'byTier'   => $base()->selectRaw('tier, count(*) as total')
                ->groupBy('tier')->orderByDesc('total')->get(),
            'byStatus' => $base()->selectRaw('status, count(*) as total')
                ->groupBy('status')->orderByDesc('total')->get(),
        ];
    }

    public function financial(array $filters = [])
    {
        $base    = fn () => $this->between(AccountTransaction::query(), 'txn_date', $filters);
        $income  = (float) $base()->where('type', 'income')->sum('amount');
        $expense = (float) $base()->where('type', 'expense')->sum('amount');

        return [
            'income'    => $income,
            'expense'   => $expense,
            'net'       => $income - $expense,
            'txns'      => $base()->count(),
            'byMonth'   => $base()->selectRaw("DATE_FORMAT(txn_date, '%Y-%m') as month")
                ->selectRaw("sum(case when type = 'income' then amount else 0 end) as income")
                ->selectRaw("sum(case when type = 'expense' then amount else 0 end) as expense")
                ->groupBy('month')->orderBy('month')->get(),
            'byAccount' => $base()->selectRaw('account_name')
                ->selectRaw("sum(case when type = 'income' then amount else 0 end) as income")
                ->selectRaw("sum(case when type = 'expense' then amount else 0 end) as expense")
                ->groupBy('account_name')->orderByDesc('income')->get(),
        ];
    }

    public function custom(array $filters = [])
    {
        $bookingRevenue = (float) $this->between(Booking::query(), 'created_at', $filters)->sum('amount');
        $income         = (float) $this->between(AccountTransaction::query(), 'txn_date', $filters)->where('type', 'income')->sum('amount');
        $expense        = (float) $this->between(AccountTransaction::query(), 'txn_date', $filters)->where('type', 'expense')->sum('amount');

        return [
            'snapshot' => collect([
                (object) ['module' => 'Bookings',     'records' => Booking::count(),          'value' => $bookingRevenue,                  'label' => 'Revenue'],
                (object) ['module' => 'Packages',     'records' => Package::count(),          'value' => (float) Package::sum('price'),    'label' => 'Catalog value'],
                (object) ['module' => 'Visa',         'records' => VisaApplication::count(),  'value' => null,                             'label' => '-'],
                (object) ['module' => 'Flights',      'records' => FlightBooking::count(),    'value' => (float) FlightBooking::sum('fare'), 'label' => 'Fare'],
                (object) ['module' => 'Hotels',       'records' => Hotel::count(),            'value' => null,                             'label' => '-'],
                (object) ['module' => 'Customers',    'records' => Customer::count(),         'value' => null,                             'label' => '-'],
                (object) ['module' => 'Agents',       'records' => AgentCommission::count(),  'value' => (float) AgentCommission::sum('amount'), 'label' => 'Commission'],
                (object) ['module' => 'Transactions', 'records' => AccountTransaction::count(), 'value' => $income - $expense,             'label' => 'Net'],
            ]),
            'totalRecords' => Booking::count() + Package::count() + VisaApplication::count()
                + FlightBooking::count() + Hotel::count() + Customer::count()
                + AgentCommission::count() + AccountTransaction::count(),
            'income'  => $income,
            'expense' => $expense,
        ];
    }

    /**
     * Build a CSV-ready export for a report type: ['filename','headers','rows'].
     * Reuses the same date-filtered data as the on-screen report.
     */
    public function export(string $type, array $filters = []): array
    {
        $suffix = (!empty($filters['from']) || !empty($filters['to']))
            ? '_' . ($filters['from'] ?? 'start') . '_to_' . ($filters['to'] ?? 'end')
            : '';

        switch ($type) {
            case 'sales':
                $d = $this->sales($filters);
                return $this->csv("sales{$suffix}", ['Status', 'Orders', 'Revenue'],
                    $d['byStatus']->map(fn ($r) => [$r->status, $r->orders, $r->revenue]));

            case 'visa':
                $d = $this->visa($filters);
                return $this->csv("visa{$suffix}", ['Country', 'Applications'],
                    $d['byCountry']->map(fn ($r) => [$r->country, $r->total]));

            case 'package':
                $d = $this->package($filters);
                return $this->csv("package{$suffix}", ['Package', 'Destination', 'Category', 'Price', 'Orders', 'Revenue'],
                    $d['byPackage']->map(fn ($r) => [$r->title, $r->destination, $r->category, $r->price, $r->orders, $r->revenue]));

            case 'flight':
                $d = $this->flight($filters);
                return $this->csv("flight{$suffix}", ['Airline', 'Bookings', 'Fare'],
                    $d['byAirline']->map(fn ($r) => [$r->airline, $r->total, $r->fare]));

            case 'hotel':
                $d = $this->hotel($filters);
                return $this->csv("hotel{$suffix}", ['City', 'Country', 'Hotels', 'Rooms', 'Avg Price'],
                    $d['byCity']->map(fn ($r) => [$r->city, $r->country, $r->hotels, $r->rooms, round($r->avg_price, 2)]));

            case 'agent':
                $d = $this->agent($filters);
                return $this->csv("agent{$suffix}", ['Status', 'Records', 'Amount'],
                    $d['byStatus']->map(fn ($r) => [$r->status, $r->records, $r->amount]));

            case 'customer':
                $d = $this->customer($filters);
                return $this->csv("customer{$suffix}", ['Tier', 'Customers'],
                    $d['byTier']->map(fn ($r) => [$r->tier, $r->total]));

            case 'financial':
                $d = $this->financial($filters);
                return $this->csv("financial{$suffix}", ['Month', 'Income', 'Expense'],
                    $d['byMonth']->map(fn ($r) => [$r->month, $r->income, $r->expense]));

            case 'custom':
            default:
                $d = $this->custom($filters);
                return $this->csv("custom{$suffix}", ['Module', 'Records', 'Value', 'Metric'],
                    $d['snapshot']->map(fn ($r) => [$r->module, $r->records, $r->value, $r->label]));
        }
    }

    private function csv(string $filename, array $headers, $rows): array
    {
        return [
            'filename' => $filename . '.csv',
            'headers'  => $headers,
            'rows'     => $rows->values()->all(),
        ];
    }
}
