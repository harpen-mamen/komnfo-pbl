<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Refuse destructive test traits against the application's database,
        // including when a previously cached configuration overrides phpunit.xml.
        $connection = config('database.default');
        if ($connection !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Tests require the isolated SQLite :memory: database. Clear cached configuration before running tests.');
        }
    }
}
