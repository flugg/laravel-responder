<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\ResponderServiceProvider;
use Orchestra\Testbench\TestCase;

/**
 * Abstract test case for bootstrapping the environment for the integration suite.
 */
abstract class IntegrationTestCase extends TestCase
{
    /**
     * Define environment variables.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        //
    }

    /**
     * Get package service providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [ResponderServiceProvider::class];
    }
}
