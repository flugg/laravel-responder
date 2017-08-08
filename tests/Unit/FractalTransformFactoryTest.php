<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\FractalTransformFactory;
use Flugg\Responder\Tests\TestCase;
use League\Fractal\Resource\NullResource;
use League\Fractal\Scope;
use League\Fractal\Serializer\SerializerAbstract;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\FractalTransformFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class FractalTransformFactoryTest extends TestCase
{
    /**
     * A mock a Fractal's [Manager] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $manager;

    /**
     * The [TransformFactory] class being tested.
     *
     * @var \Flugg\Responder\FractalTransformFactory
     */
    protected $factory;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->manager = $this->mockFractalManager();
        $this->factory = new FractalTransformFactory($this->manager);
    }

    /**
     * Assert that the [make] method uses the manager to transform data.
     */
    public function testMakeMethodShouldCallOnManager()
    {
        $this->manager->shouldReceive('createData')->andReturn($scope = Mockery::mock(Scope::class));
        $scope->shouldReceive('toArray')->andReturn($data = ['foo' => 1]);

        $result = $this->factory->make($resource = new NullResource(null, null, 'foo'), $serializer = Mockery::mock(SerializerAbstract::class), [
            'includes' => $with = ['foo'],
            'excludes' => $without = ['bar'],
            'fieldsets' => $fieldsets = [],
        ]);

        $this->assertEquals($data, $result);
        $this->manager->shouldHaveReceived('setSerializer')->with($serializer)->once();
        $this->manager->shouldHaveReceived('parseIncludes')->with($with)->once();
        $this->manager->shouldHaveReceived('parseExcludes')->with($without)->once();
        $this->manager->shouldHaveReceived('parseFieldsets')->with($fieldsets)->once();
        $this->manager->shouldHaveReceived('createData')->with($resource)->once();
    }
}