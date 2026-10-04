<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TripPlannerService
{
    public function generate(array $input): array
    {
        $key = decrypt_setting('openai_api_key');
        if (! $key) {
            throw new RuntimeException('AI Trip Planner is not configured. Add OPENAI_API_KEY.');
        }

        $prompt = sprintf(
            'Create a practical %d-day itinerary for %s starting %s. Budget: %s. Interests: %s. Return JSON only with a "days" array; each item must contain day, title, activities (array), meals, and tips.',
            $input['days'], $input['destination'], $input['start_date'], $input['budget'] ?: 'flexible', $input['interests'] ?: 'general sightseeing'
        );

        $response = Http::timeout(45)->withToken($key)->post(rtrim(settings('openai_api_url') ?: 'https://api.openai.com/v1', '/').'/chat/completions', [
            'model' => settings('openai_model') ?: 'gpt-4o-mini',
            'response_format' => ['type' => 'json_object'],
            'messages' => [['role' => 'system', 'content' => 'You are a concise expert travel planner.'], ['role' => 'user', 'content' => $prompt]],
        ])->throw()->json('choices.0.message.content');

        $plan = json_decode((string) $response, true);
        if (! is_array($plan) || ! isset($plan['days'])) throw new RuntimeException('The AI returned an invalid itinerary.');
        return $plan;
    }
}
