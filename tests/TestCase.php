<?php

namespace Tests;

use App\Helpers\SettingHelper;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    /**
     * Render Inertia pages without requiring a compiled Vite manifest, with the instance set up.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Treat the instance as already set up by default so the first-time
        // setup gate doesn't redirect every test. Setup-specific tests reset
        // this. Guarded for unit tests that don't migrate a database.
        try {
            if (Schema::hasTable('settings')) {
                SettingHelper::setSetting('instance_configured', true, 'bool');
            }
        } catch (\Throwable) {
            // No database available in this test context.
        }
    }
}
