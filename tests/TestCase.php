<?php

namespace GraphQL\Bootstrapper\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Fake events flag.
     */
    protected bool $fakeEvents = true;

    /**
     * The package providers.
     */
    protected function getPackageProviders($app): array
    {
        return [];
    }

    /**
     * Define environment.
     */
    protected function defineEnvironment($app): void
    {
        // Make sure, our .env file is loaded for local tests
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        $app['config']->set('database.default', env('DB_DRIVER', 'mysql'));

        // MySQL config
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1:3306'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'password'),
            'database' => env('DB_DATABASE', 'platform'),
            'prefix' => '',
        ]);

        if ($this->fakeEvents) {
            Event::fake();
        }
    }
}
