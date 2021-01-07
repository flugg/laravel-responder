<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Http\Decorators\ResponseDecorator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Prophecy\Exception\Prediction\PredictionException;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Abstract test case for bootstrapping the environment for the unit suite.
 */
abstract class UnitTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * An instance of a prophet class.
     *
     * @var \Prophecy\Prophet
     */
    private $prophet;

    /**
     * This method is called before each test.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->prophet = new \Prophecy\Prophet;
        Mockery::globalHelpers();
    }

    /**
     * This method is called after each test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        try {
            $this->prophet->checkPredictions();
        } catch (PredictionException $e) {
            throw new AssertionFailedError($e->getMessage());
        } finally {
            $this->addToAssertionCount(count($this->prophet->getProphecies()));
        }

        parent::tearDown();
    }

    /**
     * Create a new Prophecy mock instance from the given class or interface.
     *
     * @param string|null $classOrInterface
     * @return \Prophecy\Prophecy\ObjectProphecy
     * @throws \Prophecy\Exception\Doubler\DoubleException
     */
    protected function mock(?string $classOrInterface = null): ObjectProphecy
    {
        return $this->prophet->prophesize($classOrInterface);
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
