<?php
namespace App\Http\Controllers;

use App\Models\TripPlan;
use App\Services\{FlightSearchService,LoyaltyService,TripPlannerService};
use Illuminate\Http\Request;

class GrowthFeatureController extends Controller
{
    private function customer(Request $request) { $customer = $request->user()?->customer; abort_unless($customer, 403); return $customer; }
    public function planner(Request $request) { return view('backend.portal.customer.trip-planner', ['plans' => $this->customer($request)->tripPlans()->latest()->get()]); }
    public function generate(Request $request, TripPlannerService $planner)
    {
        $data = $request->validate(['destination'=>['required','string','max:120'],'start_date'=>['required','date','after_or_equal:today'],'days'=>['required','integer','min:1','max:30'],'budget'=>['nullable','string','max:80'],'interests'=>['nullable','string','max:500']]);
        try { $plan=$planner->generate($data); TripPlan::create($data+['customer_id'=>$this->customer($request)->id,'plan'=>$plan]); return back()->with('success','Your itinerary is ready.'); }
        catch (\Throwable $e) { report($e); return back()->with('danger',$e->getMessage())->withInput(); }
    }
    public function flights(Request $request, FlightSearchService $flights)
    {
        $data=$request->validate(['origin'=>['required','alpha','size:3'],'destination'=>['required','alpha','size:3','different:origin'],'departure_date'=>['required','date','after_or_equal:today'],'return_date'=>['nullable','date','after:departure_date'],'adults'=>['required','integer','min:1','max:9'],'currency'=>['required','alpha','size:3']]);
        try { return response()->json(['data'=>$flights->search($data)]); } catch (\Throwable $e) { report($e); return response()->json(['message'=>$e->getMessage()],503); }
    }
    public function loyalty(Request $request, LoyaltyService $loyalty)
    {
        $customer = $this->customer($request);

        return view('backend.portal.customer.loyalty', [
            'customer'     => $customer,
            'code'         => $loyalty->ensureReferralCode($customer),
            'balance'      => $loyalty->balance($customer),
            'transactions' => $customer->loyaltyTransactions()->latest()->get(),
            // A points balance is meaningless without what it buys and how it
            // may be spent, which is the whole reason redemption exists.
            'worth'        => $loyalty->valueOf($loyalty->balance($customer)),
            'rate'         => $loyalty->redeemRate(),
            'minRedeem'    => $loyalty->minRedeem(),
            'maxPercent'   => $loyalty->maxRedeemPercent(),
        ]);
    }
}
