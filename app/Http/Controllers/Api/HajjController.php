<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Actions\RegisterHajjPilgrim;
use App\Models\HajjPackage;
use App\Models\HajjPilgrim;
use App\Models\Lead;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Hajj & Umrah packages for the Customer app.
 *
 * Browsing packages is public; registering as a pilgrim requires auth and
 * creates a HajjPilgrim row the agency then processes (documents, payment).
 * `type` is Hajj | Umrah — the same endpoints serve both menu entries.
 */
class HajjController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer;

    public const TYPES = ['Hajj', 'Umrah'];

    /** Public package list. Filters: ?type=Hajj|Umrah, ?search= */
    public function packages(Request $request)
    {
        $query = HajjPackage::where('status', 'active');

        if ($request->filled('type')) {
            $type = ucfirst(strtolower($request->type));
            if (! in_array($type, self::TYPES, true)) {
                return $this->responseWithError('Invalid type. Use Hajj or Umrah.', [], 422);
            }
            $query->where('type', $type);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $packages = $query->orderBy('price')
            ->get()
            ->map(fn (HajjPackage $p) => $this->packageInfo($p));

        return $this->responseWithSuccess('Hajj packages fetched.', [
            'packages' => $packages,
        ]);
    }

    /** Public package detail. */
    public function showPackage($id)
    {
        $package = HajjPackage::where('status', 'active')->find($id);
        if (! $package) {
            return $this->responseWithError('Package not found.', [], 404);
        }

        return $this->responseWithSuccess('Package detail fetched.', [
            'package' => $this->packageInfo($package),
        ]);
    }

    /** My Hajj/Umrah registrations (auth, customer). */
    public function registrations(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $items = HajjPilgrim::with('hajjPackage')
            ->where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn (HajjPilgrim $p) => $this->pilgrimInfo($p));

        return $this->responseWithSuccess('Registrations fetched.', [
            'registrations' => $items,
        ]);
    }

    /** Register for a Hajj/Umrah package (auth, customer). */
    public function register(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can register.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'hajj_package_id' => ['required', 'integer', 'exists:hajj_packages,id'],
            'name'            => ['nullable', 'string', 'max:255'],
            'passport_no'     => ['required', 'string', 'max:60'],
            'group_name'      => ['nullable', 'string', 'max:120'],
            // Same preference fields the website's Hajj/Umrah registration form
            // collects. hajj_pilgrims has no column for them, so they ride into
            // the CRM on the lead created below.
            'phone'           => ['nullable', 'string', 'max:50'],
            'email'           => ['nullable', 'email', 'max:255'],
            'package_pref'    => ['nullable', 'string', 'max:60'],
            'pilgrims'        => ['nullable', 'integer', 'min:1', 'max:100'],
            'preferred_month' => ['nullable', 'string', 'max:60'],
            'room_sharing'    => ['nullable', 'string', 'max:60'],
            'details'         => ['nullable', 'string', 'max:3000'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $package = HajjPackage::where('status', 'active')->find($request->hajj_package_id);
        if (! $package) {
            return $this->responseWithError('Package not available.', [], 404);
        }
        if ((int) $package->seats <= 0) {
            return $this->responseWithError('No seats left in this package.', [], 422);
        }

        // Shared with the website's registration form (App\Actions), so both
        // channels create the same pilgrim, seat count and CRM lead.
        try {
            $pilgrim = app(RegisterHajjPilgrim::class)(
                $package,
                $customer,
                $request->only([
                    'name', 'passport_no', 'group_name', 'phone', 'email',
                    'package_pref', 'pilgrims', 'preferred_month', 'room_sharing', 'details',
                ]) + ['name' => $request->input('name', $customer->name)],
                'Mobile App'
            );
        } catch (\DomainException $e) {
            return $this->responseWithError($e->getMessage(), [], 422);
        }

        return $this->responseWithSuccess("{$package->type} registration submitted.", [
            'registration' => $this->pilgrimInfo($pilgrim->load('hajjPackage')),
        ], 201);
    }

    private function packageInfo(HajjPackage $p): array
    {
        return [
            'id'            => $p->id,
            'package_no'    => $p->package_no,
            'title'         => $p->title,
            'type'          => $p->type,
            'duration_days' => (int) $p->duration_days,
            'price'         => (float) $p->price,
            'seats'         => (int) $p->seats,
            'status'        => $p->status,
        ];
    }

    private function pilgrimInfo(HajjPilgrim $p): array
    {
        return [
            'id'              => $p->id,
            'pilgrim_no'      => $p->pilgrim_no,
            'name'            => $p->name,
            'passport_no'     => $p->passport_no,
            'package_id'      => $p->hajj_package_id,
            'package_title'   => $p->package_title,
            'type'            => $p->hajjPackage->type ?? null,
            'price'           => $p->hajjPackage ? (float) $p->hajjPackage->price : null,
            'group_name'      => $p->group_name,
            'payment_status'  => $p->payment_status,
            'document_status' => $p->document_status,
            'status'          => $p->status,
        ];
    }
}
