<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Report\ReportInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected $repo;

    public function __construct(ReportInterface $repo)
    {
        $this->repo = $repo;
    }

    public function sales(Request $request)
    {
        return $this->render($request, 'sales');
    }

    public function visa(Request $request)
    {
        return $this->render($request, 'visa');
    }

    public function package(Request $request)
    {
        return $this->render($request, 'package');
    }

    public function flight(Request $request)
    {
        return $this->render($request, 'flight');
    }

    public function hotel(Request $request)
    {
        return $this->render($request, 'hotel');
    }

    public function agent(Request $request)
    {
        return $this->render($request, 'agent');
    }

    public function customer(Request $request)
    {
        return $this->render($request, 'customer');
    }

    public function financial(Request $request)
    {
        return $this->render($request, 'financial');
    }

    public function custom(Request $request)
    {
        return $this->render($request, 'custom');
    }

    /**
     * Shared flow: read the date filters, stream a CSV when ?export=csv,
     * otherwise render the report view with its filtered data + the filters.
     */
    private function render(Request $request, string $type)
    {
        $filters = array_filter($request->only(['from', 'to']));

        if ($request->query('export') === 'csv') {
            return $this->streamCsv($this->repo->export($type, $filters));
        }

        $data = $this->repo->{$type}($filters);

        return view("backend.reports.{$type}", array_merge($data, ['filters' => $filters]));
    }

    /** Stream a ['filename','headers','rows'] payload as a CSV download. */
    private function streamCsv(array $export): StreamedResponse
    {
        return response()->streamDownload(function () use ($export) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $export['headers']);
            foreach ($export['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $export['filename'], ['Content-Type' => 'text/csv']);
    }
}
