<?php

namespace App\Repositories\Visa;

use App\Models\Package;
use App\Models\Customer;
use App\Models\VisaService;
use App\Models\VisaDocument;
use App\Models\VisaApplication;
use App\Repositories\BaseRepository;
use App\Repositories\Visa\VisaInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VisaRepository extends BaseRepository implements VisaInterface
{
    /** Allowed option sets — mirror the visa_applications migration. */
    public const VISA_TYPES        = ['Tourist', 'Business', 'Umrah', 'Student'];
    public const DOCUMENT_STATUSES = ['Pending', 'Submitted', 'Verified'];
    public const STATUSES          = ['Processing', 'In Review', 'Approved', 'Rejected'];
    public const APPOINTMENT_STATUSES = ['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'];

    /**
     * Passport scans and other embassy paperwork are personal documents, not
     * marketing assets — the "public" disk is symlinked straight onto the web
     * root, so anything saved there is downloadable by anyone who guesses (or
     * scrapes) the URL, no session required. The "local" disk lives under
     * storage/app with no public URL at all; VisaController::downloadDocument
     * is the only door in, and it sits behind auth + the visa_read permission.
     */
    public const DOCUMENT_DISK = 'local';

    /**
     * Missions and visa centres, keyed by the destination country.
     *
     * Typed by hand the same centre arrives as "UAE Embassy, Dhaka", "uae
     * embassy" and "VFS UAE" — and then no appointment list can be grouped or
     * counted by centre. Picking the application first narrows this to the one
     * country that can actually issue that visa.
     */
    public const EMBASSY_CENTERS = [
        'UAE'                  => ['UAE Embassy, Dhaka', 'VFS Global (UAE), Gulshan'],
        'United Arab Emirates' => ['UAE Embassy, Dhaka', 'VFS Global (UAE), Gulshan'],
        'Saudi Arabia'         => ['Saudi Consulate, Dhaka', 'Saudi Embassy, Baridhara', 'Etimad Centre, Dhaka'],
        'Schengen'             => ['VFS Schengen, Gulshan', 'Italian Embassy, Baridhara', 'German Embassy, Gulshan'],
        'Schengen (Europe)'    => ['VFS Schengen, Gulshan', 'Italian Embassy, Baridhara', 'German Embassy, Gulshan'],
        'UK'                   => ['VFS UK, Dhaka', 'British High Commission, Baridhara'],
        'United Kingdom'       => ['VFS UK, Dhaka', 'British High Commission, Baridhara'],
        'USA'                  => ['US Embassy, Madani Avenue', 'US Consular Section, Dhaka'],
        'United States'        => ['US Embassy, Madani Avenue', 'US Consular Section, Dhaka'],
        'Canada'               => ['VFS Canada, Banani', 'High Commission of Canada, Baridhara'],
        'Australia'            => ['VFS Australia, Gulshan', 'Australian High Commission, Baridhara'],
        'India'                => ['IVAC Jamuna Future Park', 'IVAC Mirpur', 'IVAC Uttara', 'Indian High Commission, Baridhara'],
        'Thailand'             => ['Thai Embassy, Dhaka', 'Thai Visa Centre, Gulshan'],
        'Malaysia'             => ['Malaysia Visa Centre (VLN), Dhaka', 'High Commission of Malaysia, Gulshan'],
        'Singapore'            => ['Singapore Visa Centre, Dhaka'],
        'China'                => ['Chinese Visa Application Centre, Dhaka', 'Embassy of China, Baridhara'],
        'Japan'                => ['Embassy of Japan, Baridhara'],
        'South Korea'          => ['Embassy of Korea, Baridhara'],
        'Turkey'               => ['Turkish Embassy, Baridhara', 'VFS Turkey, Dhaka'],
        'Qatar'                => ['Qatar Visa Centre, Dhaka', 'Embassy of Qatar, Gulshan'],
        'Kuwait'               => ['Embassy of Kuwait, Gulshan'],
        'Oman'                 => ['Embassy of Oman, Baridhara'],
        'Egypt'                => ['Embassy of Egypt, Gulshan'],
        'Indonesia'            => ['Embassy of Indonesia, Gulshan'],
        'Nepal'                => ['Embassy of Nepal, Baridhara'],
        'Sri Lanka'            => ['Sri Lanka High Commission, Gulshan'],
        'Maldives'             => ['Maldives High Commission, Gulshan'],
    ];

    /** The paperwork embassies ask for, in roughly the order a file is built. */
    public const DOCUMENT_TYPES = [
        'Passport',
        'Passport Photo',
        'National ID',
        'Birth Certificate',
        'Visa Application Form',
        'Cover Letter',
        'Bank Statement',
        'Bank Solvency Certificate',
        'Tax Return (TIN)',
        'Trade License',
        'Employment / NOC Letter',
        'Salary Certificate',
        'Invitation Letter',
        'Hotel Booking',
        'Air Ticket Booking',
        'Travel Insurance',
        'Marriage Certificate',
        'Student ID',
        'Admission Letter',
        'Vaccination Certificate',
        'Police Clearance',
        'Other',
    ];

    protected array $with = ['customer', 'package'];

    public function __construct(VisaApplication $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'customer_id'      => $request->customer_id ?: null,
            'package_id'       => $request->package_id ?: null,
            'visa_service_id'  => $request->visa_service_id ?: null,
            'application_no'   => $request->application_no,
            'applicant_name'   => $request->applicant_name,
            'country'          => $request->country,
            'visa_type'        => $request->visa_type,
            'govt_fee'         => $request->filled('govt_fee') ? $request->govt_fee : null,
            'service_fee'      => $request->filled('service_fee') ? $request->service_fee : null,
            'applied_date'     => $request->applied_date ?: null,
            'appointment_date' => $request->appointment_date ?: null,
            'expiry_date'      => $request->expiry_date ?: null,
            'documents_status' => $request->documents_status,
            'status'           => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('application_no', 'like', "%{$search}%")
                    ->orWhere('applicant_name', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['visa_type'])) {
            $query->where('visa_type', $filters['visa_type']);
        }
    }

    public function formData(): array
    {
        return [
            'customers'        => Customer::orderBy('name')->get(['id', 'name']),
            'packages'         => Package::orderBy('title')->get(['id', 'title']),
            'visaServices'     => VisaService::active()->ordered()
                ->get(['id', 'country', 'visa_type', 'govt_fee', 'service_fee']),
            'visaTypes'        => self::VISA_TYPES,
            'documentStatuses' => self::DOCUMENT_STATUSES,
            'statuses'         => self::STATUSES,
        ];
    }

    /* ---------------------------------------------------------------------
     | Visa management pages
     * ------------------------------------------------------------------- */

    public function dashboard()
    {
        $byCountry = VisaApplication::selectRaw('country, COUNT(*) c')
            ->groupBy('country')->orderByDesc('c')->take(6)->get();

        $byStatus = VisaApplication::selectRaw('status, COUNT(*) c')->groupBy('status')->get();

        return [
            'total'      => VisaApplication::count(),
            'approved'   => VisaApplication::where('status', 'Approved')->count(),
            'processing' => VisaApplication::whereIn('status', ['Processing', 'In Review'])->count(),
            'rejected'   => VisaApplication::where('status', 'Rejected')->count(),
            'recent'     => VisaApplication::latest()->take(8)->get(),
            'byCountry'  => [
                'labels' => $byCountry->pluck('country')->all(),
                'series' => $byCountry->pluck('c')->map(fn ($c) => (int) $c)->all(),
            ],
            'byStatus'   => [
                'labels' => $byStatus->pluck('status')->all(),
                'series' => $byStatus->pluck('c')->map(fn ($c) => (int) $c)->all(),
            ],
        ];
    }

    public function applicationDetails($id)
    {
        return ['application' => $this->find($id)];
    }

    public function appointment()
    {
        return [
            'appointments' => VisaApplication::whereNotNull('appointment_date')
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get(),
            'applications' => VisaApplication::orderBy('applicant_name')->get(),
            'canBookAppointment' => VisaApplication::whereNull('appointment_date')->exists(),
            'appointmentStatuses' => self::APPOINTMENT_STATUSES,
        ];
    }

    public function appointmentFormData()
    {
        return [
            'applications' => VisaApplication::whereNull('appointment_date')
                ->orderBy('applicant_name')
                ->get(),
            'appointmentStatuses' => self::APPOINTMENT_STATUSES,
            'embassyCenters'      => $this->embassyCenters(),
        ];
    }

    public function documents()
    {
        return [
            'documents' => VisaDocument::with('visaApplication')
                ->latest('uploaded_at')
                ->latest('id')
                ->get(),
            'applications' => VisaApplication::orderBy('applicant_name')->get(),
        ];
    }

    public function documentFormData()
    {
        return [
            'applications'  => VisaApplication::orderBy('applicant_name')->get(),
            'documentTypes' => $this->documentTypes(),
        ];
    }

    /**
     * The document checklist an embassy actually asks for.
     *
     * Typed free-hand, the same paper arrives as "Passport", "passport copy"
     * and "PP" — and then no screen can tell which applicant is missing what.
     * Anything already on file is merged in, so older spellings and any type a
     * particular embassy needs stay selectable.
     */
    /**
     * country => centres, with whatever is already on file merged in so an
     * agency's own centre (and any older spelling) stays selectable.
     */
    public function embassyCenters(): array
    {
        $centers = collect(self::EMBASSY_CENTERS)->map(fn ($list) => collect($list));

        VisaApplication::whereNotNull('embassy_center')
            ->get(['country', 'embassy_center'])
            ->each(function ($row) use ($centers) {
                $country = $row->country ?: 'Other';
                $centers->put($country, $centers->get($country, collect())->push($row->embassy_center));
            });

        return $centers
            ->map(fn ($list) => $list->filter()->unique()->sort()->values()->all())
            ->filter()
            ->all();
    }

    public function documentTypes(): array
    {
        return collect(self::DOCUMENT_TYPES)
            ->merge(VisaDocument::distinct()->orderBy('document_type')->pluck('document_type'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function uploadDocument($request)
    {
        $path = null;

        try {
            $application = $this->find($request->visa_application_id);
            $file = $request->file('document');
            $path = $file->storeAs(
                "visa-documents/{$application->id}",
                (string) \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension(),
                self::DOCUMENT_DISK
            );

            if (! $path) {
                throw new \RuntimeException('Document storage failed.');
            }

            DB::transaction(function () use ($application, $file, $path, $request) {
                $application->documents()->create([
                    'document_type' => $request->document_type,
                    'file_name'     => $file->getClientOriginalName(),
                    'file_path'     => $path,
                    'mime_type'     => $file->getMimeType(),
                    'status'        => $request->status,
                    'uploaded_at'   => now(),
                ]);

                if ($application->documents_status !== 'Verified') {
                    $application->documents_status = $request->status;
                    $application->save();
                }
            });

            $this->logActivity('updated', $application);

            return $this->responseWithSuccess('Visa document uploaded successfully.');
        } catch (\Throwable $th) {
            if ($path) {
                Storage::disk(self::DOCUMENT_DISK)->delete($path);
            }

            return $this->responseWithError('Unable to upload the visa document.');
        }
    }

    public function findDocument($id)
    {
        return VisaDocument::findOrFail($id);
    }

    public function updateDocument($request)
    {
        $newPath = null;

        try {
            $document = $this->findDocument($request->id);
            $oldPath = $document->file_path;
            $file = $request->file('document');

            if ($file) {
                $newPath = $file->storeAs(
                    "visa-documents/{$document->visa_application_id}",
                    (string) \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension(),
                    self::DOCUMENT_DISK
                );

                if (! $newPath) {
                    throw new \RuntimeException('Document storage failed.');
                }
            }

            DB::transaction(function () use ($document, $request, $file, $newPath) {
                $data = [
                    'document_type' => $request->document_type,
                    'status'        => $request->status,
                ];

                if ($file) {
                    $data += [
                        'file_name'   => $file->getClientOriginalName(),
                        'file_path'   => $newPath,
                        'mime_type'   => $file->getMimeType(),
                        'uploaded_at' => now(),
                    ];
                }

                $document->update($data);
                $this->syncDocumentStatus($document->visaApplication);
            });

            if ($newPath && $oldPath !== $newPath) {
                Storage::disk(self::DOCUMENT_DISK)->delete($oldPath);
            }

            $this->logActivity('updated', $document->visaApplication);

            return $this->responseWithSuccess('Visa document updated successfully.');
        } catch (\Throwable $th) {
            if ($newPath) {
                Storage::disk(self::DOCUMENT_DISK)->delete($newPath);
            }

            return $this->responseWithError('Unable to update the visa document.');
        }
    }

    public function deleteDocument($id)
    {
        try {
            $document = $this->findDocument($id);
            $application = $document->visaApplication;
            $path = $document->file_path;

            DB::transaction(function () use ($document, $application) {
                $document->delete();
                $this->syncDocumentStatus($application);
            });

            Storage::disk(self::DOCUMENT_DISK)->delete($path);
            $this->logActivity('updated', $application);

            return $this->responseWithSuccess('Visa document deleted successfully.');
        } catch (\Throwable $th) {
            return $this->responseWithError('Unable to delete the visa document.');
        }
    }

    public function bookAppointment($request)
    {
        try {
            $application = $this->find($request->id);

            if ($request->isMethod('post') && $application->appointment_date) {
                return $this->responseWithError('This visa application already has an appointment.');
            }

            $application->update([
                'embassy_center'     => $request->embassy_center,
                'appointment_date'   => $request->appointment_date,
                'appointment_time'   => $request->appointment_time,
                'appointment_status' => $request->appointment_status,
                'appointment_notes'  => $request->appointment_notes,
            ]);

            $this->logActivity('updated', $application);

            return $this->responseWithSuccess('Embassy appointment saved successfully.');
        } catch (\Throwable $th) {
            report($th);

            return $this->responseWithError('Unable to save the embassy appointment.');
        }
    }

    public function deleteAppointment($id)
    {
        try {
            $application = $this->find($id);
            $application->update([
                'appointment_date'   => null,
                'embassy_center'     => null,
                'appointment_time'   => null,
                'appointment_status' => null,
                'appointment_notes'  => null,
            ]);

            $this->logActivity('updated', $application);

            return $this->responseWithSuccess('Embassy appointment deleted successfully.');
        } catch (\Throwable $th) {
            return $this->responseWithError('Unable to delete the embassy appointment.');
        }
    }

    private function syncDocumentStatus(VisaApplication $application): void
    {
        $statuses = $application->documents()->pluck('status');

        $application->documents_status = $statuses->contains('Verified')
            ? 'Verified'
            : ($statuses->isNotEmpty() ? 'Submitted' : 'Pending');
        $application->save();
    }

    public function tracking()
    {
        return [
            // Open cases first — those are the ones staff act on; decided cases
            // stay visible underneath for reference.
            'applications' => VisaApplication::with('customer')
                ->orderByRaw("CASE WHEN status IN ('Approved', 'Rejected') THEN 1 ELSE 0 END")
                ->orderByRaw("CASE status WHEN 'In Review' THEN 0 ELSE 1 END")
                ->latest('applied_date')
                ->latest('id')
                ->get(),
            'statuses'          => self::STATUSES,
            'documentStatuses'  => self::DOCUMENT_STATUSES,
        ];
    }

    /**
     * Move a case along. Only the transitions the business actually makes are
     * allowed, so a decided case can't silently drop back into processing.
     */
    public function advance($request, $id)
    {
        try {
            $application = $this->find($id);

            if (! $application) {
                return $this->responseWithError(___('alert.not_found'));
            }

            $status    = $request->input('status', $application->status);
            $documents = $request->input('documents_status', $application->documents_status);

            if (! $this->canMoveTo($application->status, $status)) {
                $article = in_array($application->status[0], ['A', 'E', 'I', 'O', 'U'], true) ? 'An' : 'A';

                return $this->responseWithError(
                    "{$article} {$application->status} application cannot move to {$status}."
                );
            }

            $application->status           = $status;
            $application->documents_status = $documents;

            // An application is only "applied" once it reaches the embassy;
            // stamp that the first time it leaves the desk.
            if ($status === 'In Review' && ! $application->applied_date) {
                $application->applied_date = now()->toDateString();
            }

            if ($request->filled('expiry_date')) {
                $application->expiry_date = $request->input('expiry_date');
            }

            $application->save();

            $this->logActivity('updated', $application);
            $this->tellCustomer($application);

            return $this->responseWithSuccess('Application updated.');
        } catch (\Throwable $th) {
            return $this->responseWithError('Unable to update the application.');
        }
    }

    /** Allowed status moves; a decided case is final. */
    private function canMoveTo(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return match ($from) {
            'Processing' => in_array($to, ['In Review', 'Approved', 'Rejected'], true),
            'In Review'  => in_array($to, ['Approved', 'Rejected', 'Processing'], true),
            default      => false,
        };
    }

    /** Status changes are what customers chase, so the portal is told. */
    private function tellCustomer(VisaApplication $application): void
    {
        if (! $application->customer) {
            return;
        }

        \App\Models\Notification::notify(
            $application->customer,
            "Visa application {$application->status}",
            "{$application->application_no} for {$application->country} is now {$application->status}.",
            'visa'
        );
    }

    public function expiry()
    {
        // Only an issued visa can expire. A case still Processing or Rejected
        // has no validity to run out, so counting one here would put work on
        // the desk that does not exist.
        $issued = fn () => VisaApplication::with('customer')
            ->where('status', 'Approved')
            ->whereNotNull('expiry_date');

        $upcoming = $issued()
            ->where('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date')
            ->get();

        // An expired visa is a renewal lead too, so it is listed separately
        // rather than dropped.
        return [
            'applications' => $upcoming,
            'expired'      => $issued()
                ->where('expiry_date', '<', now()->toDateString())
                ->orderByDesc('expiry_date')
                ->take(20)
                ->get(),
            // Separate bands, not running totals: a visa expiring in 14 days
            // belongs to the first tile only. Counting it in all three made
            // every tile read "1" and told the office nothing about urgency.
            'within15'     => $upcoming->filter(fn ($a) => $a->daysToExpiry() <= 15)->count(),
            'within30'     => $upcoming->filter(fn ($a) => $a->daysToExpiry() > 15 && $a->daysToExpiry() <= 30)->count(),
            'within90'     => $upcoming->filter(fn ($a) => $a->daysToExpiry() > 30 && $a->daysToExpiry() <= 90)->count(),
        ];
    }

    /**
     * Nudge a holder about an approaching expiry. Renewals are the cheapest
     * repeat sale there is, and they are lost by simply not asking.
     */
    public function notifyExpiry($id)
    {
        try {
            $application = $this->find($id);

            if (! $application?->customer) {
                return $this->responseWithError('This application has no customer to notify.');
            }

            $days = $application->daysToExpiry();

            \App\Models\Notification::notify(
                $application->customer,
                'Visa expiring soon',
                "Your {$application->country} visa expires on "
                    . $application->expiry_date->format('d M Y')
                    . ($days !== null && $days >= 0 ? " ({$days} days left)." : '.')
                    . ' Reply to start a renewal.',
                'visa'
            );

            $this->logActivity('updated', $application);

            return $this->responseWithSuccess('Renewal reminder sent to ' . $application->customer->name . '.');
        } catch (\Throwable $th) {
            return $this->responseWithError('Unable to send the reminder.');
        }
    }

    public function reports()
    {
        return [
            'byStatus'  => VisaApplication::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'byCountry' => VisaApplication::selectRaw('country, count(*) as total')->groupBy('country')->pluck('total', 'country'),
            // Money only exists on cases linked to a catalogue entry, so the
            // grouping is by service rather than by the free-text country.
            'byService' => VisaApplication::selectRaw(
                'country, visa_type, count(*) as total,'
                . ' sum(coalesce(govt_fee, 0)) as govt_total,'
                . ' sum(coalesce(service_fee, 0)) as service_total'
            )
                ->whereNotNull('visa_service_id')
                ->groupBy('country', 'visa_type')
                ->orderByDesc('service_total')
                ->get(),
            'earned'    => (float) VisaApplication::sum('service_fee'),
            'collected' => (float) VisaApplication::selectRaw(
                'sum(coalesce(govt_fee, 0) + coalesce(service_fee, 0)) as total'
            )->value('total'),
        ];
    }
}
