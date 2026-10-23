<?php

namespace App\Services\Reports;

use App\Models\Lead;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeadAttributionReport
{
    private const WEBSITE = "COALESCE(NULLIF(TRIM(leads.website), ''), 'Unspecified')";

    private const LANGUAGE = "CASE LOWER(TRIM(leads.language)) WHEN 'english' THEN 'English' WHEN 'french' THEN 'French' WHEN 'italian' THEN 'Italian' WHEN 'spanish' THEN 'Spanish' ELSE COALESCE(NULLIF(TRIM(leads.language), ''), 'Unspecified') END";

    public function build(array $filters = []): array
    {
        $leadRows = $this->dimensions($this->period(DB::table('leads'), 'leads.created_at', $filters), $filters)
            ->selectRaw(self::WEBSITE.' as website, '.self::LANGUAGE.' as language')
            ->selectRaw("COUNT(*) as leads, SUM(CASE WHEN LOWER(stage) = 'won' THEN 1 ELSE 0 END) as won")
            ->selectRaw("SUM(COALESCE(value, 0)) as potential_value, SUM(CASE WHEN LOWER(stage) = 'won' THEN COALESCE(value, 0) ELSE 0 END) as won_value")
            ->groupByRaw(self::WEBSITE.', '.self::LANGUAGE)->get();

        $sales = $this->sales($filters);
        $revenueRows = $this->dimensions(DB::query()->fromSub(clone $sales, 'sales')
            ->join('leads', 'leads.id', '=', 'sales.lead_id'), $filters)
            ->selectRaw(self::WEBSITE.' as website, '.self::LANGUAGE.' as language')
            ->selectRaw('COUNT(*) as bookings, SUM(sales.amount) as revenue')
            ->groupByRaw(self::WEBSITE.', '.self::LANGUAGE)->get();

        $websites = collect(config('lead-reporting.websites', []))
            ->merge(\Illuminate\Support\Facades\Schema::hasTable('dmc_websites') ? \App\Models\Dmc\Website::pluck('name') : [])
            ->merge(DB::table('leads')->selectRaw(self::WEBSITE.' as website')->distinct()->pluck('website'))
            ->map(fn ($website) => trim($website))->filter()->unique()->sort()->values();

        $languages = collect(Lead::LANGUAGES)->merge($leadRows->pluck('language'))
            ->merge($revenueRows->pluck('language'))->unique()->values();
        $reportWebsites = empty($filters['website']) ? ($websites->isEmpty() ? collect(['Unspecified']) : $websites) : collect([$filters['website']]);
        $reportLanguages = empty($filters['language']) ? $languages : collect([$filters['language']]);
        $matrix = collect();
        $leadIndex = $leadRows->keyBy(fn ($row) => json_encode([$row->website, $row->language]));
        $revenueIndex = $revenueRows->keyBy(fn ($row) => json_encode([$row->website, $row->language]));

        foreach ($reportWebsites as $website) {
            foreach ($reportLanguages as $language) {
                $key = json_encode([$website, $language]);
                $lead = $leadIndex->get($key);
                $revenue = $revenueIndex->get($key);
                $matrix->push((object) [
                    'website' => $website,
                    'language' => $language,
                    'leads' => (int) ($lead->leads ?? 0),
                    'won' => (int) ($lead->won ?? 0),
                    'potential_value' => (float) ($lead->potential_value ?? 0),
                    'won_value' => (float) ($lead->won_value ?? 0),
                    'bookings' => (int) ($revenue->bookings ?? 0),
                    'revenue' => (float) ($revenue->revenue ?? 0),
                ]);
            }
        }

        return [
            'matrix' => $matrix,
            'byWebsite' => $matrix->groupBy('website')->map(fn ($rows, $name) => $this->summarize($rows) + ['name' => $name])->values(),
            'byLanguage' => $matrix->groupBy('language')->map(fn ($rows, $name) => $this->summarize($rows) + ['name' => $name])->values(),
            'totals' => $this->summarize($matrix),
            'websites' => $websites,
            'languages' => $languages,
            'unattributedRevenue' => (float) DB::query()->fromSub($sales, 'sales')->whereNull('lead_id')->sum('amount'),
        ];
    }

    private function summarize(Collection $rows): array
    {
        return collect(['leads', 'won', 'potential_value', 'won_value', 'bookings', 'revenue'])
            ->mapWithKeys(fn ($metric) => [$metric => $rows->sum($metric)])->all();
    }

    private function dimensions(Builder $query, array $filters): Builder
    {
        if (! empty($filters['website'])) {
            $query->whereRaw(self::WEBSITE.' = ?', [$filters['website']]);
        }
        if (! empty($filters['language'])) {
            $query->whereRaw(self::LANGUAGE.' = ?', [$filters['language']]);
        }

        return $query;
    }

    private function period(Builder $query, string $column, array $filters): Builder
    {
        if (! empty($filters['from'])) {
            $query->whereDate($column, '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate($column, '<=', $filters['to']);
        }

        return $query;
    }

    private function sales(array $filters): Builder
    {
        $packages = $this->period(DB::table('bookings'), 'created_at', $filters)
            ->select('lead_id', 'amount')->whereIn('status', ['confirmed', 'paid']);
        $flights = $this->period(DB::table('flight_bookings'), 'created_at', $filters)
            ->selectRaw('lead_id, fare as amount')->whereIn('status', ['Confirmed', 'Reissued'])
            ->whereNull('booking_id');
        $hotels = $this->period(DB::table('hotel_bookings'), 'created_at', $filters)
            ->select('lead_id', 'amount')->whereIn('status', ['Confirmed', 'Paid']);

        return $packages->unionAll($flights)->unionAll($hotels);
    }
}
