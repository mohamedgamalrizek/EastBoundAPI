<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripPlan;
use App\Services\{FlightSearchService,LoyaltyService,TripPlannerService};
use App\Traits\{ApiReturnFormatTrait,ResolvesCustomer};
use Illuminate\Http\Request;

class GrowthFeatureController extends Controller {
 use ApiReturnFormatTrait, ResolvesCustomer;
 public function plans(Request $r) { $c=$this->customer($r); if(!$c)return $this->responseWithError('Customers only.',[],403); return $this->responseWithSuccess('Trip plans fetched.',['plans'=>$c->tripPlans()->latest()->get()]); }
 public function generate(Request $r, TripPlannerService $planner) { $c=$this->customer($r); if(!$c)return $this->responseWithError('Customers only.',[],403); $d=$r->validate(['destination'=>'required|string|max:120','start_date'=>'required|date|after_or_equal:today','days'=>'required|integer|min:1|max:30','budget'=>'nullable|string|max:80','interests'=>'nullable|string|max:500']); try{$plan=TripPlan::create($d+['customer_id'=>$c->id,'plan'=>$planner->generate($d)]);return $this->responseWithSuccess('Itinerary ready.',['plan'=>$plan],201);}catch(\Throwable $e){report($e);return $this->responseWithError($e->getMessage(),[],503);} }
 public function flights(Request $r, FlightSearchService $service) { $d=$r->validate(['origin'=>'required|alpha|size:3','destination'=>'required|alpha|size:3|different:origin','departure_date'=>'required|date|after_or_equal:today','return_date'=>'nullable|date|after:departure_date','adults'=>'required|integer|min:1|max:9','currency'=>'required|alpha|size:3']); try{return $this->responseWithSuccess('Live flights fetched.',['flights'=>$service->search($d)]);}catch(\Throwable $e){report($e);return $this->responseWithError($e->getMessage(),[],503);} }
 public function loyalty(Request $r, LoyaltyService $service)
 {
  $c = $this->customer($r);
  if (! $c) return $this->responseWithError('Customers only.', [], 403);

  $balance = $service->balance($c);

  return $this->responseWithSuccess('Loyalty fetched.', [
   'points'        => $balance,
   'referral_code' => $service->ensureReferralCode($c),
   'transactions'  => $c->loyaltyTransactions()->latest()->get(),
   // Without these the app can show a balance but never let anyone spend
   // it, which is exactly the state this endpoint used to leave it in.
   'redeem' => [
    'rate'            => $service->redeemRate(),
    'value'           => $service->valueOf($balance),
    'min'             => $service->minRedeem(),
    'max_percent'     => $service->maxRedeemPercent(),
    'enabled'         => $service->redeemRate() > 0,
    'can_redeem_now'  => $service->redeemRate() > 0 && $balance >= $service->minRedeem(),
   ],
  ]);
 }
}
