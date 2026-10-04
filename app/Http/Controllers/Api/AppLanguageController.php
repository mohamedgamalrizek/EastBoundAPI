<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Backend\Language;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * App localisation: the languages the admin manages at
 * Settings > App Language, plus the mobile phrase pack for each one.
 *
 * Phrases live in lang/{code}/mobile.json so the existing phrase editor
 * (App Language > edit phrase, module "mobile") edits what the app shows.
 * A language without its own file falls back to English rather than
 * answering 404 and leaving the app stuck on cached text.
 */
class AppLanguageController extends Controller
{
    use ApiReturnFormatTrait;

    /** GET /app-languages — active languages for the in-app picker. */
    public function languages()
    {
        $languages = Language::where('status', Status::ACTIVE->value)
            ->orderBy('name')
            ->get()
            ->map(fn (Language $lang) => [
                'id'             => $lang->id,
                'name'           => $lang->name,
                'code'           => $lang->code,
                'text_direction' => strtolower($lang->text_direction ?: 'ltr'),
                'status'         => (int) $lang->getRawOriginal('status'),
            ])
            ->values();

        return $this->responseWithSuccess('Languages fetched.', $languages);
    }

    /** GET /app-language/terms?language_code=bn — the phrase pack. */
    public function terms(Request $request)
    {
        $code = trim((string) $request->input('language_code')) ?: 'en';

        $language = Language::where('code', $code)
            ->where('status', Status::ACTIVE->value)
            ->first();

        // Unknown or disabled code → serve English so the app always renders.
        if (! $language) {
            $code     = 'en';
            $language = Language::where('code', 'en')->first();
        }

        return $this->responseWithSuccess('Terms fetched.', [
            'language_name'  => $language->name ?? 'English',
            'language_code'  => $code,
            'text_direction' => strtolower(($language->text_direction ?? null) ?: 'ltr'),
            'terms'          => $this->phrases($code),
        ]);
    }

    /** The mobile phrase file for a code, falling back to English. */
    private function phrases(string $code): array
    {
        foreach ([$code, 'en'] as $candidate) {
            $path = base_path("lang/{$candidate}/mobile.json");

            if (File::exists($path)) {
                $terms = json_decode(File::get($path), true);

                if (is_array($terms)) {
                    return $terms;
                }
            }
        }

        return [];
    }
}
