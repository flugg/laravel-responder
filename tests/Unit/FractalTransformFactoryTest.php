<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\FractalTransformFactory;
use Flugg\Responder\Tests\TestCase;
use League\Fractal\Resource\NullResource;
use League\Fractal\Scope;
use League\Fractal\Serializer\SerializerAbstract;
use LogicException;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\FractalTransformFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class FractalTransformFactoryTest extends TestCase
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->mockFractalManager();
        $this->factory = new FractalTransformFactory($this->manager);
    }

    /**
     * Assert that the [make] method uses the manager to transform data.
     */
    public function testMakeMethodShouldCallOnManager(): void
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

    /**
     * Assert that the [make] method throws a [LogicException] when fieldsets are requested, but
     * the resource doesn't have a resource key.
     */
    public function testMakeMethodShouldThrowExceptionIfResourceKeyIsNotSetAndFieldsetsAreRequested(): void
    {
        $this->manager->shouldReceive('createData')->andReturn($scope = Mockery::mock(Scope::class));
        $scope->shouldReceive('toArray')->andReturn($data = ['foo' => 1]);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Filtering fields using sparse fieldsets require resource key to be set.');

        $this->factory->make($resource = new NullResource, $serializer = Mockery::mock(SerializerAbstract::class), [
            'fieldsets' => $fieldsets = ['foo'],
        ]);
    }

    /**
     * Assert that the [make] method parses fieldsets
     */
    public function testMakeMethodShouldParseFieldsets(): void
    {
        $this->manager->shouldReceive('createData')->andReturn($scope = Mockery::mock(Scope::class));
        $scope->shouldReceive('toArray')->andReturn($data = ['foo' => 1]);

        $this->factory->make($resource = new NullResource(null, null, 'foo'), $serializer = Mockery::mock(SerializerAbstract::class), [
            'fieldsets' => $fieldsets = ['id', 'name'],
        ]);

        $this->manager->shouldHaveReceived('parseFieldsets')->with([
            'foo' => 'id,name',
        ])->once();
    }

    /**
     * Assert that the [make] method parses fieldsets
     */
    public function testMakeMethodShouldParseFieldsetsWithNested(): void
    {
        $this->manager->shouldReceive('createData')->andReturn($scope = Mockery::mock(Scope::class));
        $scope->shouldReceive('toArray')->andReturn($data = ['foo' => 1]);

        $this->factory->make($resource = new NullResource(null, null, 'foo'), $serializer = Mockery::mock(SerializerAbstract::class), [
            'includes' => $with = ['bar.baz'],
            'fieldsets' => $fieldsets = [
                'foo' => ['id', 'name'],
                'bar' => ['id'],
            ],
        ]);

        $this->manager->shouldHaveReceived('parseFieldsets')->with([
            'foo' => 'id,name,bar',
            'bar' => 'id,baz',
        ])->once();
    }
}