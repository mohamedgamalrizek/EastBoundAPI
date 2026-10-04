<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Actions\OpenVisaCaseFromEnquiry;
use App\Models\VisaApplication;
use App\Models\VisaDocument;
use App\Models\VisaService;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Repositories\Visa\VisaRepository;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Visa status tracking for the Customer app (read-only).
 * New applications are filed by the agency; the customer tracks progress.
 */
class VisaController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer;

    /**
     * Public visa catalogue — the same VisaService rows, filters and order the
     * website's Visa Services page lists. Filters: ?q= (country), ?type=.
     */
    public function services(Request $request)
    {
        $query = VisaService::active()->ordered();

        if ($q = $request->input('q')) {
            $query->where('country', 'like', "%{$q}%");
        }
        if ($type = $request->input('type')) {
            $query->where('visa_type', $type);
        }

        $services = $query->get()->map(fn (VisaService $s) => [
            'id'              => $s->id,
            'country'         => $s->country,
            'flag'            => $s->flag,
            'visa_type'       => $s->visa_type,
            'processing_time' => $s->processing_time,
            'stay_duration'   => $s->stay_duration,
            'entry_type'      => $s->entry_type,
            'fee'             => (float) $s->fee,
            // Split so the app can show what is passed through to the embassy
            // and what the agency charges, instead of one unexplained figure.
            'govt_fee'        => (float) $s->govt_fee,
            'service_fee'     => (float) $s->service_fee,
            'requirements'    => $s->requirementList(),
            'is_featured'     => (bool) $s->is_featured,
        ]);

        return $this->responseWithSuccess('Visa services fetched.', [
            'services' => $services,
            'types'    => VisaService::active()->distinct()->orderBy('visa_type')->pluck('visa_type'),
        ]);
    }

    /**
     * Public status tracking by application reference — the app counterpart of
     * the website's Track Your Visa page, timeline included.
     */
    public function track(Request $request)
    {
        $ref = trim((string) $request->input('ref'));
        if ($ref === '') {
            return $this->responseWithError('Enter your visa application reference.', [], 422);
        }

        $visa = VisaApplication::where('application_no', $ref)->first();
        if (! $visa) {
            return $this->responseWithError('No application found for this reference.', [], 404);
        }

        return $this->responseWithSuccess('Visa application found.', [
            'application' => $this->visaInfo($visa),
            'timeline'    => $this->timeline($visa),
        ]);
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $items = VisaApplication::where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn (VisaApplication $v) => $this->visaInfo($v));

        return $this->responseWithSuccess('Visa applications fetched.', [
            'applications' => $items,
        ]);
    }

    public function show(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $visa = VisaApplication::where('customer_id', $customer->id)->with('documents')->find($id);
        if (! $visa) {
            return $this->responseWithError('Visa application not found.', [], 404);
        }

        return $this->responseWithSuccess('Visa detail fetched.', [
            'application' => $this->visaInfo($visa),
            // Metadata only — no file_path, so the app never sees a storage
            // location. Downloading goes through downloadDocument() below,
            // which re-checks ownership before touching the disk.
            'documents'   => $visa->documents->map(fn (VisaDocument $d) => [
                'id'            => $d->id,
                'document_type' => $d->document_type,
                'file_name'     => $d->file_name,
                'status'        => $d->status,
                'uploaded_at'   => optional($d->uploaded_at)->toDateTimeString(),
            ]),
        ]);
    }

    /**
     * Stream a visa document to its owner. The document must belong to an
     * application filed by the signed-in customer — anyone else's reference
     * gets the same 404 a non-existent id would, so this can't be used to
     * probe which document ids exist.
     */
    public function downloadDocument(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $document = VisaDocument::whereHas(
            'visaApplication',
            fn ($q) => $q->where('customer_id', $customer->id)
        )->find($id);

        if (! $document) {
            return $this->responseWithError('Document not found.', [], 404);
        }

        $disk = VisaRepository::DOCUMENT_DISK;

        if (! Storage::disk($disk)->exists($document->file_path)) {
            return $this->responseWithError('Document not found.', [], 404);
        }

        return Storage::disk($disk)->download($document->file_path, $document->file_name);
    }

    /** Apply for a new visa (auth, customer). */
    public function apply(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can apply.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'country'        => ['required', 'string', 'max:120'],
            'visa_type'      => ['required', 'string', 'max:120'],
            'applicant_name' => ['nullable', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        // Same action as the website form. It used to build the row here with
        // `status: pending` and `documents_status: Collecting` — neither is in
        // the module's vocabulary, so an app-created case showed in no tile and
        // the desk could never advance it (there is no transition rule for a
        // status that does not exist).
        $visa = app(OpenVisaCaseFromEnquiry::class)($customer, [
            'name'      => $request->input('applicant_name', $customer->name),
            'phone'     => $customer->phone,
            'email'     => $customer->email,
            'country'   => $request->country,
            'visa_type' => $request->visa_type,
        ]);

        Notification::notify($customer, 'Visa application started',
            "Your {$visa->country} visa application {$visa->application_no} has been created.", 'visa');

        return $this->responseWithSuccess('Visa application created.', [
            'application' => $this->visaInfo($visa),
        ], 201);
    }

    /**
     * The five tracking stages the website renders, with the first unfinished
     * stage marked active. State is done | active | '' so the app can style it
     * the same way.
     */
    private function timeline(VisaApplication $app): array
    {
        $docsDone = in_array($app->documents_status, ['Submitted', 'Verified'], true);
        $lodged   = (bool) $app->applied_date;
        $decided  = in_array($app->status, ['Approved', 'Rejected'], true);
        $approved = $app->status === 'Approved';

        $stages = [
            [
                'title' => 'Documents received',
                'time'  => $docsDone ? $app->documents_status : 'Awaiting your documents',
                'done'  => $docsDone,
            ],
            [
                'title' => 'Application lodged',
                'time'  => $lodged ? 'Filed on ' . dateFormat($app->applied_date) : 'Not filed yet',
                'done'  => $lodged,
            ],
            [
                'title' => 'Under processing',
                'time'  => $app->appointment_date
                    ? 'Appointment ' . dateFormat($app->appointment_date)
                    : 'Embassy review',
                'done'  => $decided,
            ],
            [
                'title' => 'Decision',
                'time'  => $decided ? $app->status : 'Awaiting outcome',
                'done'  => $decided,
            ],
            [
                'title' => $approved ? 'Visa ready' : 'Collection',
                'time'  => $approved
                    ? ($app->expiry_date ? 'Valid until ' . dateFormat($app->expiry_date) : 'Ready to collect')
                    : 'Pending approval',
                'done'  => $approved,
            ],
        ];

        $markedActive = false;

        return array_map(function ($stage) use (&$markedActive) {
            $stage['state'] = $stage['done'] ? 'done' : (! $markedActive ? 'active' : '');
            $markedActive   = $markedActive || ! $stage['done'];

            return $stage;
        }, $stages);
    }

    private function visaInfo(VisaApplication $v): array
    {
        return [
            'id'               => $v->id,
            'application_no'   => $v->application_no ?: ('VISA-' . str_pad((string) $v->id, 5, '0', STR_PAD_LEFT)),
            'applicant_name'   => $v->applicant_name,
            'country'          => $v->country,
            'visa_type'        => $v->visa_type,
            'applied_date'     => optional($v->applied_date)->toDateString(),
            'appointment_date' => optional($v->appointment_date)->toDateString(),
            'expiry_date'      => optional($v->expiry_date)->toDateString(),
            'documents_status' => $v->documents_status,
            'status'           => $v->status,
        ];
    }
}
