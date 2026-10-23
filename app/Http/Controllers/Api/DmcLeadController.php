<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dmc\Website;
use App\Models\Lead;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DmcLeadController extends Controller
{
    use ApiReturnFormatTrait;

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'website' => ['required', 'string', Rule::exists('dmc_websites', 'slug')->where('active', true)],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'required_without:phone', 'email', 'max:255'],
                'phone' => ['nullable', 'required_without:email', 'string', 'max:50'],
                'language' => ['required', Rule::in(Lead::LANGUAGES)],
                'country' => ['required', 'string', 'max:100'],
                'campaign_source' => ['required', Rule::in(Lead::CAMPAIGN_SOURCES)],
                'campaign_id' => ['nullable', 'required_if:campaign_source,Google Ads', 'integer', 'exists:campaigns,id'],
                'subject' => ['required', 'string', 'max:255'],
                'comments' => ['nullable', 'string', 'max:5000'],
                'external_reference' => ['nullable', 'string', 'max:120'],
            ]);
            $created = false;
            $lead = DB::transaction(function () use ($data, &$created) {
                $created = false;
                $website = Website::where('slug', $data['website'])->where('active', true)->lockForUpdate()->firstOrFail();
                $canonical = $data;
                ksort($canonical);
                $hash = hash('sha256', json_encode($canonical, JSON_THROW_ON_ERROR));
                if (filled($data['external_reference'] ?? null)) {
                    $existing = Lead::where('dmc_website_id', $website->id)->where('dmc_external_reference', $data['external_reference'])->first();
                    if ($existing) {
                        if (! hash_equals($existing->dmc_payload_hash ?? '', $hash)) {
                            throw ValidationException::withMessages(['external_reference' => 'This delivery reference has already been used for a different lead payload.']);
                        }

                        return $existing;
                    }
                }
                $created = true;

                return Lead::create(array_merge($data, [
                    'website' => $website->name, 'dmc_website_id' => $website->id,
                    'dmc_external_reference' => $data['external_reference'] ?? null, 'dmc_payload_hash' => $hash,
                    'source' => 'Website', 'stage' => 'New', 'interest' => $data['subject'],
                ]));
            }, 3);

            return $this->responseWithSuccess('Lead received.', ['lead' => $lead], $created ? 201 : 200);
        } catch (ValidationException $exception) {
            return $this->responseWithError('Validation failed.', $exception->errors(), 422);
        }
    }
}
