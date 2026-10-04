<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\CustomerPortal\CustomerPortalInterface;
use App\Http\Requests\Portal\PortalTravelerRequest;
use App\Http\Requests\Portal\PortalPassportRequest;
use App\Http\Requests\Portal\PortalTicketRequest;
use App\Http\Requests\Portal\PortalSettingsRequest;
use App\Http\Requests\Portal\PortalBookTourRequest;
use App\Http\Requests\Portal\PortalBookHotelRequest;
use App\Http\Requests\Portal\PortalBookTransportRequest;
use App\Http\Requests\Portal\PortalPayRequest;
use App\Http\Requests\Portal\PortalReviewRequest;
use Illuminate\Http\Request;

class CustomerPortalController extends Controller
{
    protected $repo;

    public function __construct(CustomerPortalInterface $repo)
    {
        $this->repo = $repo;
    }

    public function dashboard()
    {
        return view('backend.portal.customer.dashboard', $this->repo->dashboard());
    }

    public function supportCreate()
    {
        return view('backend.portal.customer.support-create', $this->repo->support());
    }

    public function supportStore(PortalTicketRequest $request)
    {
        return $this->portalRedirect($this->repo->storeTicket($request), 'cust.support');
    }

    public function passport()
    {
        return view('backend.portal.customer.passport', $this->repo->passport());
    }

    public function passportCreate()
    {
        return view('backend.portal.customer.passport-create', $this->repo->passport());
    }

    public function passportStore(PortalPassportRequest $request)
    {
        return $this->portalRedirect($this->repo->storePassport($request), 'cust.passport');
    }

    public function passportEdit($id)
    {
        return view('backend.portal.customer.passport-edit', array_merge(
            ['passport' => $this->repo->findOwnPassport($id)],
            $this->repo->passport()
        ));
    }

    public function passportUpdate(PortalPassportRequest $request)
    {
        return $this->portalRedirect($this->repo->updatePassport($request), 'cust.passport');
    }

    public function passportDelete($id)
    {
        $result = $this->repo->deletePassport($id);

        return response()->json($result, $result['status_code']);
    }

    public function travelers()
    {
        return view('backend.portal.customer.travelers', $this->repo->travelers());
    }

    public function travelersCreate()
    {
        return view('backend.portal.customer.travelers-create', $this->repo->travelers());
    }

    public function travelersStore(PortalTravelerRequest $request)
    {
        return $this->portalRedirect($this->repo->storeTraveler($request), 'cust.travelers');
    }

    public function travelersEdit($id)
    {
        return view('backend.portal.customer.travelers-edit', array_merge(
            ['traveler' => $this->repo->findOwnTraveler($id)],
            $this->repo->travelers()
        ));
    }

    public function travelersUpdate(PortalTravelerRequest $request)
    {
        return $this->portalRedirect($this->repo->updateTraveler($request), 'cust.travelers');
    }

    public function travelersDelete($id)
    {
        $result = $this->repo->deleteTraveler($id);

        return response()->json($result, $result['status_code']);
    }

    /** Redirect a self-service CRUD result back to its portal list. */
    /**
     * Hand the payer over to the provider's hosted checkout. They come back to
     * the public payment callback, which settles the booking — nothing is paid
     * on this request.
     */
    private function gatewayRedirect(array $checkout, string $route)
    {
        if (! empty($checkout['status']) && ! empty($checkout['redirect'])) {
            return redirect()->away($checkout['redirect']);
        }

        return back()->with('danger', $checkout['message'] ?: ___('alert.something_went_wrong'))->withInput();
    }

    private function portalRedirect(array $result, string $route)
    {
        if ($result['status']) {
            return redirect()->route($route)->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function bookings()
    {
        return view('backend.portal.customer.bookings', $this->repo->bookings());
    }

    public function tours()
    {
        return view('backend.portal.customer.tours', $this->repo->tours());
    }

    public function visa()
    {
        return view('backend.portal.customer.visa', $this->repo->visa());
    }

    /**
     * A customer may only ever fetch a document off their own application —
     * ownedVisaDocument() does that scoping, so a 404 here means either the
     * document doesn't exist or it belongs to someone else, never "which".
     */
    public function downloadVisaDocument($id)
    {
        $document = $this->repo->ownedVisaDocument($id);

        if (! $document) {
            abort(404);
        }

        $disk = \App\Repositories\Visa\VisaRepository::DOCUMENT_DISK;

        abort_unless(\Illuminate\Support\Facades\Storage::disk($disk)->exists($document->file_path), 404);

        return \Illuminate\Support\Facades\Storage::disk($disk)->download($document->file_path, $document->file_name);
    }

    public function flights()
    {
        return view('backend.portal.customer.flights', $this->repo->flights());
    }

    public function hotels()
    {
        return view('backend.portal.customer.hotels', $this->repo->hotels());
    }

    public function transport()
    {
        return view('backend.portal.customer.transport', $this->repo->transport());
    }

    // ---- Self-service: booking & payment ----

    public function bookTour(PortalBookTourRequest $request)
    {
        return $this->portalRedirect($this->repo->bookTour($request), 'cust.bookings');
    }

    /**
     * Live price for the Book modal — answers JSON so the customer sees what
     * a code or a points slider is worth before committing to the trip.
     */
    public function quoteTour(Request $request)
    {
        return response()->json($this->repo->quoteTour($request));
    }

    public function reviews()
    {
        return view('backend.portal.customer.reviews', $this->repo->reviews());
    }

    public function reviewStore(PortalReviewRequest $request)
    {
        return $this->portalRedirect($this->repo->storeReview($request), 'cust.reviews');
    }

    public function payBooking(PortalPayRequest $request)
    {
        if ($checkout = $this->repo->beginOnlinePayment($request, 'booking')) {
            return $this->gatewayRedirect($checkout, 'cust.bookings');
        }

        return $this->portalRedirect($this->repo->payBooking($request), 'cust.bookings');
    }

    public function bookHotel(PortalBookHotelRequest $request)
    {
        return $this->portalRedirect($this->repo->bookHotel($request), 'cust.hotels');
    }

    public function payHotel(PortalPayRequest $request)
    {
        if ($checkout = $this->repo->beginOnlinePayment($request, 'hotel')) {
            return $this->gatewayRedirect($checkout, 'cust.hotels');
        }

        return $this->portalRedirect($this->repo->payHotel($request), 'cust.hotels');
    }

    public function requestTransport(PortalBookTransportRequest $request)
    {
        return $this->portalRedirect($this->repo->requestTransport($request), 'cust.transport');
    }

    public function payTransport(PortalPayRequest $request)
    {
        if ($checkout = $this->repo->beginOnlinePayment($request, 'transport')) {
            return $this->gatewayRedirect($checkout, 'cust.transport');
        }

        return $this->portalRedirect($this->repo->payTransport($request), 'cust.transport');
    }

    /**
     * PDF of one of the customer's own documents. The repository does the
     * ownership check, so a guessed id 404s instead of leaking paperwork.
     */
    public function documentPdf(string $kind, $id)
    {
        $model = $this->repo->ownedDocument($kind, $id);

        if (! $model) {
            abort(404);
        }

        $documents = app(\App\Services\Documents\DocumentService::class);

        return match ($kind) {
            'invoice' => $documents->invoice($model)->download($documents->filename('invoice', $model->invoice_no)),
            'receipt' => $documents->receipt($model)->download($documents->filename('receipt', $model->receipt_no)),
            'hotel'   => $documents->hotelVoucher($model)->download($documents->filename('voucher', $model->booking_no)),
        };
    }

    public function invoices()
    {
        return view('backend.portal.customer.invoices', $this->repo->invoices());
    }

    public function payments()
    {
        return view('backend.portal.customer.payments', $this->repo->payments());
    }

    public function wallet()
    {
        return view('backend.portal.customer.wallet', $this->repo->wallet());
    }

    public function documents()
    {
        return view('backend.portal.customer.documents', $this->repo->documents());
    }

    public function support()
    {
        return view('backend.portal.customer.support', $this->repo->support());
    }

    public function settings()
    {
        return view('backend.portal.customer.settings', $this->repo->settings());
    }

    public function settingsUpdate(PortalSettingsRequest $request)
    {
        return $this->portalRedirect($this->repo->updateSettings($request), 'cust.settings');
    }

    public function wishlist()
    {
        return view('backend.portal.customer.wishlist', $this->repo->wishlist());
    }

    public function wishlistDelete($id)
    {
        $result = $this->repo->removeWishlist($id);

        return response()->json($result, $result['status_code']);
    }
}
