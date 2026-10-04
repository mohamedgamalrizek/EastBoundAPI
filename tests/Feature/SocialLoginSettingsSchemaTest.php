<?php

namespace Tests\Feature;

use App\Models\Backend\Setting;
use Illuminate\Database\Schema\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SocialLoginSettingsSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_settings_value_column_is_large_enough_for_encrypted_social_secrets(): void
    {
        $this->assertSame('text', Schema::getColumnType('settings', 'value'));

        $secret = str_repeat('a', 512);
        $encrypted = encrypt($secret);

        Setting::updateOrCreate(['key' => 'google_client_secret'], ['value' => $encrypted]);

        $this->assertSame($encrypted, Setting::where('key', 'google_client_secret')->value('value'));
        $this->assertSame($secret, decrypt(Setting::where('key', 'google_client_secret')->value('value')));
    }
}
