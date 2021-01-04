<?php

namespace Flugg\Responder\Tests;

use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test case for bootstrapping the environment for the unit suite.
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

class ModelWithGetResourceKey extends Model
{
    public function getResourceKey()
    {
        //
    }
}
