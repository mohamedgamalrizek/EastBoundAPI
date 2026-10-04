<?php

namespace Tests\Feature;

use App\Models\Backend\Setting;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Uploading artwork through Settings → General.
 *
 * The assertion that matters is the shape of the stored path. It used to be
 * written with a Windows separator baked into the needle, so on Linux and macOS
 * the absolute filesystem path was stored verbatim and every image uploaded
 * from the admin panel resolved to a placeholder — logos, favicon, everything.
 */
class AppLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
    }

    public function test_an_uploaded_logo_is_stored_relative_to_public_and_served(): void
    {
        // The seeder now ships app artwork, so this starts populated. Replacing
        // it reuses the same Upload row and rewrites its path, so the setting's
        // id is expected to stay put — the path is what must change.
        $before = (string) Setting::where('key', 'app_logo_dark')->value('value');
        $beforePath = (string) Upload::where('id', $before)->value('original');

        $this->actingAs($this->admin())
            ->put(route('settings.update'), [
                'app_logo_dark' => UploadedFile::fake()->image('app-logo-dark.png', 653, 146),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        Cache::forget(tenant_cache_prefix() . 'settings');

        $stored = (string) Setting::where('key', 'app_logo_dark')->value('value');
        $this->assertNotSame('', $stored, 'The upload should leave an Upload id on the setting.');


        $upload = Upload::find($stored);
        $this->assertNotNull($upload);
        $this->assertNotSame($beforePath, $upload->original, 'The upload should replace the previous file.');
        $this->assertStringStartsWith('uploads/', $upload->original, 'Stored path must be relative to public/.');
        $this->assertFileExists(public_path($upload->original));

        // and the API serves the uploaded file, not a placeholder
        $data = $this->getJson('/api/v1/settings')->assertOk()->json('data');
        $this->assertStringNotContainsString('placehold', $data['app_logo_dark']);
        $this->assertStringContainsString($upload->original, $data['app_logo_dark']);
    }
}
