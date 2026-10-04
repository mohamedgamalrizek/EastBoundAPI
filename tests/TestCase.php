<?php

namespace Tests;

use App\Http\Middleware\EnsureAppKey;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // A fresh install seeds EnsureAppKey::DEFAULT_KEY, so every API call
        // in the suite carries it the way the shipped app does. Tests that
        // exercise the check itself override the header or the setting.
        $this->withHeader('X-App-Key', EnsureAppKey::DEFAULT_KEY);
    }
}
