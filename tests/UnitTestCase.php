<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Http\Decorators\ResponseDecorator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Abstract test case for bootstrapping the environment for the unit suite.
 */
abstract class UnitTestCase extends TestCase
{
    use ProphecyTrait;
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

/** Stub model with a [getResourceKey] method. */
class ModelWithGetResourceKey extends Model
{
    public function getResourceKey()
    {
        //
    }
}

/** Stub class to increase status code by one. */
class IncreaseStatusByOneDecorator extends ResponseDecorator
{
    public function make(array $data, int $status, array $headers = []): JsonResponse
    {
        return parent::make($data, $status + 1, $headers);
    }
}
