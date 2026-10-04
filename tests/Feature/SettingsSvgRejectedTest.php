<?php

namespace Tests\Feature;

use App\Http\Requests\Settings\GeneralSettingsUpdateRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * A logo/favicon rendered inline (straight into <head>/<body> on every page)
 * that accepted SVG uploads would let an attacker plant a stored-XSS payload
 * — an SVG can carry a <script> or an onload handler that runs the moment
 * the browser paints it, no further interaction needed.
 *
 * GeneralSettingsUpdateRequest::rules() is not wired to a live route in this
 * build (grep finds no controller referencing the class), so this pins the
 * validation contract directly rather than through an HTTP round trip: svg
 * must never be back on either mimes list, whichever controller ends up
 * using it.
 */
class SettingsSvgRejectedTest extends TestCase
{
    private function rules(): array
    {
        return (new GeneralSettingsUpdateRequest())->rules();
    }

    public function test_an_svg_logo_is_rejected(): void
    {
        $svg = UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml');

        $validator = Validator::make(['logo' => $svg], ['logo' => $this->rules()['logo']]);

        $this->assertTrue($validator->fails(), 'An SVG logo must fail validation.');
        $this->assertStringNotContainsString('svg', $this->rules()['logo']);
    }

    public function test_an_svg_favicon_is_rejected(): void
    {
        $svg = UploadedFile::fake()->create('favicon.svg', 5, 'image/svg+xml');

        $validator = Validator::make(['favicon' => $svg], ['favicon' => $this->rules()['favicon']]);

        $this->assertTrue($validator->fails(), 'An SVG favicon must fail validation.');
        $this->assertStringNotContainsString('svg', $this->rules()['favicon']);
    }

    public function test_a_png_logo_is_still_accepted(): void
    {
        $png = UploadedFile::fake()->image('logo.png', 200, 80);

        $validator = Validator::make(['logo' => $png], ['logo' => $this->rules()['logo']]);

        $this->assertFalse($validator->fails(), 'A plain PNG logo must still be accepted: ' . $validator->errors());
    }
}
