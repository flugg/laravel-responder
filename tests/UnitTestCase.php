<?php

namespace Flugg\Responder\Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * A base test case for bootstrapping the testing environment for the unit suite.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class UnitTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Mockery::globalHelpers();
    }
}
