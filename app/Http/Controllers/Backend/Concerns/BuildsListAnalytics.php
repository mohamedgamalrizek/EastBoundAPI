<?php

namespace App\Http\Controllers\Backend\Concerns;

use Illuminate\Support\Carbon;

/**
 * Helpers that turn a model into <x-list-analytics> chart payloads.
 * Used by list controllers to build donut (group-by) + trend (last 6
 * months) series without repeating the same query shape everywhere.
 */
trait BuildsListAnalytics
{
    /**
     * Build a full analytics payload from a declarative config, e.g.
     *   ['model' => 'Task', 'group' => 'status', 'label' => 'Tasks']
     *   ['model' => 'AgentInvoice', 'group' => 'status', 'sum' => 'amount',
     *    'label' => 'Invoices', 'sumLabel' => 'Total Billed']
     * 'model' is a short App\Models name (or a full class). 'group' is the
     * donut column (null = no donut). 'sum' adds a ৳ KPI + a revenue trend.
     */
    protected function autoAnalytics(array $cfg): array
    {
        $model = str_contains($cfg['model'], '\\') ? $cfg['model'] : 'App\\Models\\' . $cfg['model'];
        $group = array_key_exists('group', $cfg) ? $cfg['group'] : 'status';
        $sum   = $cfg['sum']   ?? null;
        $date  = $cfg['date']  ?? 'created_at';
        $label = $cfg['label'] ?? 'Records';

        $stats = ['Total ' . $label => number_format($model::count())];
        if ($sum) {
            $stats[$cfg['sumLabel'] ?? 'Total Amount'] = currency_symbol() . number_format((float) $model::sum($sum));
        }
        $stats['This Month'] = number_format(
            (int) $model::where($date, '>=', Carbon::now()->startOfMonth())->count()
        );

        $donut = null;
        if ($group) {
            $donut = $this->laGroup($model, $group);
            if (!empty($donut['labels'])) {
                $stats[(string) $donut['labels'][0]] = $donut['series'][0];
            }
        }

        $trend = $sum
            ? $this->laMonthly($model, $label, "SUM($sum)", 'area', $date, 'float')
            : $this->laMonthly($model, $label, 'COUNT(*)', 'bar', $date, 'int');

        return ['stats' => $stats, 'donut' => $donut, 'trend' => $trend];
    }

    /** Group rows by a column → donut payload ['labels','series']. */
    protected function laGroup(string $model, string $col, string $agg = 'COUNT(*)', string $cast = 'int'): array
    {
        $rows = $model::selectRaw("$col as k, $agg as v")
            ->groupBy($col)->orderByDesc('v')->get();

        return [
            'labels' => $rows->pluck('k')->map(fn ($s) => ucfirst((string) $s))->all(),
            'series' => $rows->pluck('v')->map(fn ($v) => $cast === 'float' ? (float) $v : (int) $v)->all(),
        ];
    }

    /** Last-6-month trend payload ['type','labels','series']. */
    protected function laMonthly(string $model, string $name, string $agg = 'COUNT(*)', string $type = 'bar', string $dateCol = 'created_at', string $cast = 'int'): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));

        $data = $model::selectRaw("DATE_FORMAT($dateCol, '%Y-%m') ym, $agg v")
            ->where($dateCol, '>=', $months->first())
            ->groupBy('ym')->pluck('v', 'ym');

        return [
            'type'   => $type,
            'labels' => $months->map(fn ($m) => $m->format('M'))->all(),
            'series' => [[
                'name' => $name,
                'data' => $months->map(fn ($m) => $cast === 'float'
                    ? (float) ($data[$m->format('Y-m')] ?? 0)
                    : (int) ($data[$m->format('Y-m')] ?? 0))->all(),
            ]],
        ];
    }
}
