<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\VisaApplication;
use App\Http\Controllers\Controller;
use App\Repositories\Visa\VisaInterface;
use App\Repositories\Visa\VisaRepository;
use App\Http\Requests\Visa\StoreVisaRequest;
use App\Http\Requests\Visa\UpdateVisaRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Visa\UploadVisaDocumentRequest;
use App\Http\Requests\Visa\BookVisaAppointmentRequest;
use App\Http\Requests\Visa\UpdateVisaDocumentRequest;

class VisaController extends Controller
{
    protected $repo;

    public function __construct(VisaInterface $repo)
    {
        $this->repo = $repo;
    }

    /* ---------------------------------------------------------------------
     | CRUD — Visa Applications
     * ------------------------------------------------------------------- */

    public function applications(Request $request)
    {
        $applications = $this->repo->all($request->only(['search', 'status', 'visa_type']));
        $analytics    = $this->analytics();

        return view('backend.visa.applications', compact('applications', 'analytics'));
    }

    /** KPI tiles + charts for the visa applications list (over all records). */
    private function analytics(): array
    {
        $byStatus = VisaApplication::selectRaw('status, COUNT(*) c')->groupBy('status')->get();

        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));
        $filed  = VisaApplication::selectRaw("DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c")
            ->where('created_at', '>=', $months->first())
            ->groupBy('ym')->pluck('c', 'ym');

        return [
            'stats' => [
                'Total Applications' => $byStatus->sum('c'),
                'Approved'           => (int) $byStatus->firstWhere('status', 'Approved')?->c,
                'Processing'         => (int) $byStatus->firstWhere('status', 'Processing')?->c,
                // Was missing, so the page rendered a blank fifth tile while
                // the donut beside it counted In Review perfectly well.
                'In Review'          => (int) $byStatus->firstWhere('status', 'In Review')?->c,
                'Rejected'           => (int) $byStatus->firstWhere('status', 'Rejected')?->c,
            ],
            'donut' => [
                'labels' => $byStatus->pluck('status')->all(),
                'series' => $byStatus->pluck('c')->map(fn ($c) => (int) $c)->all(),
            ],
            'trend' => [
                'type'   => 'bar',
                'labels' => $months->map(fn ($m) => $m->format('M'))->all(),
                'series' => [
                    ['name' => 'Applications', 'data' => $months->map(fn ($m) => (int) ($filed[$m->format('Y-m')] ?? 0))->all()],
                ],
            ],
        ];
    }

    public function applicationDetails($id)
    {
        return view('backend.visa.application-details', $this->repo->applicationDetails($id));
    }

    public function create()
    {
        return view('backend.visa.create', $this->repo->formData());
    }

    public function store(StoreVisaRequest $request)
    {
        $result = $this->repo->store($request);

        if ($result['status']) {
            return redirect()->route('visa.applications')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function edit($id)
    {
        $application = $this->repo->find($id);

        return view('backend.visa.edit', array_merge(['application' => $application], $this->repo->formData()));
    }

    public function update(UpdateVisaRequest $request)
    {
        $result = $this->repo->update($request);

        if ($result['status']) {
            return redirect()->route('visa.applications')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }

    /* ---------------------------------------------------------------------
     | Visa management pages
     * ------------------------------------------------------------------- */

    public function dashboard()
    {
        return view('backend.visa.dashboard', $this->repo->dashboard());
    }

    public function appointment()
    {
        return view('backend.visa.appointment', $this->repo->appointment());
    }

    public function appointmentCreate()
    {
        $data = $this->repo->appointmentFormData();

        if ($data['applications']->isEmpty()) {
            return redirect()->route('visa.appointment')
                ->with('danger', 'Every visa application already has an appointment.');
        }

        return view('backend.visa.appointment-create', $data);
    }

    public function appointmentEdit($id)
    {
        $appointment = $this->repo->find($id);
        abort_if(is_null($appointment->appointment_date), 404);

        return view('backend.visa.appointment-edit', array_merge(
            ['appointment' => $appointment],
            $this->repo->appointmentFormData()
        ));
    }

    public function bookAppointment(BookVisaAppointmentRequest $request)
    {
        $result = $this->repo->bookAppointment($request);

        if ($result['status']) {
            return redirect()->route('visa.appointment')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }

    public function updateAppointment(BookVisaAppointmentRequest $request)
    {
        $result = $this->repo->bookAppointment($request);

        if ($result['status']) {
            return redirect()->route('visa.appointment')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }

    public function deleteAppointment($id)
    {
        $result = $this->repo->deleteAppointment($id);

        return response()->json($result, $result['status_code']);
    }

    public function documents()
    {
        return view('backend.visa.documents', $this->repo->documents());
    }

    public function documentCreate()
    {
        $data = $this->repo->documentFormData();

        if ($data['applications']->isEmpty()) {
            return redirect()->route('visa.documents')
                ->with('danger', 'Create a visa application before uploading documents.');
        }

        return view('backend.visa.document-create', $data);
    }

    public function documentEdit($id)
    {
        return view('backend.visa.document-edit', [
            'document'      => $this->repo->findDocument($id),
            'documentTypes' => $this->repo->documentTypes(),
        ]);
    }

    public function uploadDocument(UploadVisaDocumentRequest $request)
    {
        $result = $this->repo->uploadDocument($request);

        if ($result['status']) {
            return redirect()->route('visa.documents')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }

    public function updateDocument(UpdateVisaDocumentRequest $request)
    {
        $result = $this->repo->updateDocument($request);

        if ($result['status']) {
            return redirect()->route('visa.documents')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }

    public function deleteDocument($id)
    {
        $result = $this->repo->deleteDocument($id);

        return response()->json($result, $result['status_code']);
    }

    /**
     * Visa documents live on the "local" disk (storage/app), which has no
     * public URL — this route, gated by auth + visa_read, is the only way to
     * fetch one. See VisaRepository::DOCUMENT_DISK for why.
     */
    public function downloadDocument($id)
    {
        $document = $this->repo->findDocument($id);

        abort_unless(Storage::disk(VisaRepository::DOCUMENT_DISK)->exists($document->file_path), 404);

        return Storage::disk(VisaRepository::DOCUMENT_DISK)->download($document->file_path, $document->file_name);
    }

    public function tracking()
    {
        return view('backend.visa.tracking', $this->repo->tracking());
    }

    public function advance(Request $request, $id)
    {
        $result = $this->repo->advance($request, $id);

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }

    public function expiry()
    {
        return view('backend.visa.expiry', $this->repo->expiry());
    }

    public function notifyExpiry($id)
    {
        $result = $this->repo->notifyExpiry($id);

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }

    public function reports()
    {
        return view('backend.visa.reports', $this->repo->reports());
    }
}
