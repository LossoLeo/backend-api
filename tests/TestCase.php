<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Setup before each test.
     */
    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if (isset($uses[\Illuminate\Foundation\Testing\RefreshDatabase::class])) {
            $this->beforeApplicationDestroyed(function () {
                DB::statement('PRAGMA foreign_keys = OFF');
            });

            $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        }

        return $uses;
    }
}
