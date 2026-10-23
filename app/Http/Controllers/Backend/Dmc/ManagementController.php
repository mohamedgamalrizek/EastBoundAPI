<?php

namespace App\Http\Controllers\Backend\Dmc;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Dmc\Trip;
use App\Models\Dmc\TripAlert;
use App\Models\Dmc\Website;
use App\Models\Lead;
use App\Services\Dmc\ManagementReports;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    public function customerPreferences(Request $request)
    {
        $data = $request->validate(['dietary' => ['nullable', 'string', 'max:2000'], 'accessibility' => ['nullable', 'string', 'max:2000'], 'interests' => ['nullable', 'string', 'max:2000']]);
        $customer = Customer::findOrFail($request->route('customer'));
        $preferences = $customer->preferences ?? [];
        $preferences['dmc'] = $data;
        $customer->update(['preferences' => $preferences]);

        return redirect()->route('customer.show', $customer->id)->with('success', 'Customer preferences updated.');
    }

    public function commercialDocument(Request $request)
    {
        $module = $request->route('module');
        $model = config('dmc-modules.'.$module.'.model');
        $record = $model::findOrFail($request->route('record'));

        return Pdf::loadView('pdf.dmc-commercial', compact('module', 'record'))->download($module.'-'.$record->id.'.pdf');
    }

    public function report(Request $request, ManagementReports $reports)
    {
        $type = $request->route('type');
        abort_unless(in_array($type, ['receivables', 'profitability', 'cashflow', 'marketing', 'sales']), 404);
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => array_merge(['nullable', 'date_format:Y-m-d'], $request->filled('from') ? ['after_or_equal:from'] : []),
            'export' => ['nullable', 'in:csv'],
        ]);
        $data = in_array($type, ['marketing', 'sales']) ? $reports->{$type}($filters) : $reports->{$type}();
        if ($request->query('export') === 'csv') {
            return response()->streamDownload(function () use ($data) {
                $stream = fopen('php://output', 'w');
                fputcsv($stream, $data['headers']);
                foreach ($data['rows'] as $row) {
                    fputcsv($stream, array_map(fn ($value) => is_string($value) && preg_match('/^[=+@\-]/', ltrim($value)) ? "'".$value : $value, $row));
                }
                fclose($stream);
            }, 'dmc-'.$type.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('backend.dmc.report', $data + ['filters' => $filters, 'type' => $type]);
    }

    public function lostLeads()
    {
        return view('backend.crm.leads', ['leads' => Lead::where('stage', 'Lost')->latest()->paginate(25), 'leadListTitle' => 'Lost Leads']);
    }

    public function attribution()
    {
        $query = Lead::where(function ($query) {
            $query->whereNull('dmc_website_id')->orWhereNull('language')->orWhere('language', '')
                ->orWhereNull('country')->orWhere('country', '')->orWhereNull('campaign_source')->orWhere('campaign_source', '');
        });

        return view('backend.dmc.attribution', ['incomplete' => $query->latest()->paginate(25),
            'websites' => Website::orderBy('name')->get()]);
    }

    public function documents(Request $request)
    {
        if (! $request->route('trip')) {
            return view('backend.dmc.documents', ['trips' => Trip::with('customer')->latest()->paginate(25)]);
        }
        $trip = Trip::with(['customer', 'items.quotationItem', 'items.hotel', 'items.driver', 'items.guide', 'items.activity', 'items.supplier'])
            ->findOrFail($request->route('trip'));
        if ($request->query('format') === 'pdf') {
            return Pdf::loadView('pdf.dmc-trip', compact('trip'))->download('trip-'.$trip->id.'-vouchers.pdf');
        }

        return view('backend.dmc.trip-documents', compact('trip'));
    }

    public function alerts()
    {
        return view('backend.dmc.alerts', ['alerts' => TripAlert::with(['trip', 'task'])->latest()->paginate(25)]);
    }
}
