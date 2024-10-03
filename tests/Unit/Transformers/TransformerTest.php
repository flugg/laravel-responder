<?php

namespace Flugg\Responder\Tests\Unit\Transformers;

use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformers\Transformer;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Manager;
use League\Fractal\ParamBag;
use League\Fractal\Scope;
use LogicException;
use Mockery;

/**
 * Unit tests for the abstract [Flugg\Responder\Transformers\Transformer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class TransformerTest extends TestCase
{
    /**
     * The [Transformer] class being tested.
     *
     * @var \Flugg\Responder\Transformers\Transformer
     */
    protected $transformer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->transformer = Mockery::mock(Transformer::class);
    }

    /**
     * Assert that the [getAvailableIncludes] method returns set relations.
     */
    public function testGetAvailableIncludesMethodReturnsRelations(): void
    {
        $transformer = new TransformerWithWhitelist;

        $includes = $transformer->getAvailableIncludes();

        $this->assertEquals(['foo', 'bar'], $includes);
    }

    /**
     * Assert that the [getAvailableIncludes] method returns resolved relations when wildcard is set.
     */
    public function testGetAvailableIncludesMethodReturnsResolvedRelationsOnWildcard(): void
    {
        $transformer = new TransformerWithWildcard;
        $transformer->setCurrentScope($scope = Mockery::mock(Scope::class));
        $scope->shouldReceive('getParentScopes')->andReturn([]);
        $scope->shouldReceive('getManager')->andReturn($manager = Mockery::mock(Manager::class));
        $manager->shouldReceive('getRequestedIncludes')->andReturn(['foo', 'bar.baz']);

        $result = $transformer->getAvailableIncludes();

        $this->assertEquals(['foo', 'bar'], $result);
    }

    /**
     * Assert that the [getDefaultIncludes] method returns default relation names.
     */
    public function testGetDefaultIncludesMethodReturnsResolvedRelationsOnWildcard(): void
    {
        $transformer = new TransformerWithDefaultRelations;
        $transformer->setCurrentScope($scope = Mockery::mock(Scope::class));
        $scope->shouldReceive('getParentScopes')->andReturn([]);
        $scope->shouldReceive('getManager')->andReturn($manager = Mockery::mock(Manager::class));
        $manager->shouldReceive('getRequestedIncludes')->andReturn(['foo', 'bar']);

        $result = $transformer->getDefaultIncludes();

        $this->assertEquals(['foo', 'bar'], $result);
    }

    /**
     * Assert that the [processIncludedResources] method makes a resource from include method
     * if one exists.
     */
    public function testProcessIncludedResourcesMethodMakesResource(): void
    {
        $transformer = new TransformerWithIncludeMethod;
        $transformer->setCurrentScope($scope = Mockery::mock(Scope::class));
        $scope->shouldReceive('getParentScopes')->andReturn([]);
        $scope->shouldReceive('isRequested')->andReturn(true);
        $scope->shouldReceive('isExcluded')->andReturn(false);
        $scope->shouldReceive('getIdentifier')->andReturn('foo');
        $scope->shouldReceive('embedChildScope')->andReturn($childScope = Mockery::mock(Scope::class));
        $childScope->shouldReceive('getResource')->andReturn($this->mockResource());
        $childScope->shouldReceive('toArray')->andReturn($childData = ['id' => 2]);
        $scope->shouldReceive('getManager')->andReturn($manager = Mockery::mock(Manager::class));
        $manager->shouldReceive('getRequestedIncludes')->andReturn([]);
        $manager->shouldReceive('getIncludeParams')->andReturn(new ParamBag([]));

        $result = $transformer->processIncludedResources($scope, $data = ['id' => 1]);

        $this->assertEquals(['foo' => $childData], $result);
    }

    /**
     * Assert that the [processIncludedResources] method makes a resource implicitly if the
     * data is an Eloquent model.
     */
    public function testProcessIncludedResourcesMethodMakesResourceImplicitlyWhenGivenModel(): void
    {
        $transformer = new TransformerWithIncludeMethod;
        $transformer->setCurrentScope($scope = Mockery::mock(Scope::class));
        $scope->shouldReceive('getParentScopes')->andReturn([]);
        $scope->shouldReceive('isRequested')->andReturn(true);
        $scope->shouldReceive('isExcluded')->andReturn(false);
        $scope->shouldReceive('getIdentifier')->andReturn('foo');
        $scope->shouldReceive('embedChildScope')->andReturn($childScope = Mockery::mock(Scope::class));
        $childScope->shouldReceive('getResource')->andReturn($this->mockResource());
        $childScope->shouldReceive('toArray')->andReturn($childData = ['id' => 2]);
        $scope->shouldReceive('getManager')->andReturn($manager = Mockery::mock(Manager::class));
        $manager->shouldReceive('getRequestedIncludes')->andReturn([]);
        $manager->shouldReceive('getIncludeParams')->andReturn(new ParamBag([]));
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->andReturn('foo');

        $result = $transformer->processIncludedResources($scope, $model);

        $this->assertEquals(['foo' => $childData], $result);
    }

    /**
     * Assert that the [processIncludedResources] method makes a resource implicitly if the
     * data is an Eloquent model.
     */
    public function testProcessIncludedResourcesMethodThrowsExceptionWhenNoRelationCanBeResolved(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Relation [foo] not found in [' . TransformerWithWhitelist::class . '].');
        $transformer = new TransformerWithWhitelist;
        $transformer->setCurrentScope($scope = Mockery::mock(Scope::class));
        $scope->shouldReceive('getParentScopes')->andReturn([]);
        $scope->shouldReceive('isRequested')->andReturn(true);
        $scope->shouldReceive('isExcluded')->andReturn(false);
        $scope->shouldReceive('getIdentifier')->andReturn('foo');
        $scope->shouldReceive('getManager')->andReturn($manager = Mockery::mock(Manager::class));
        $manager->shouldReceive('getRequestedIncludes')->andReturn([]);
        $manager->shouldReceive('getIncludeParams')->andReturn(new ParamBag([]));

        $transformer->processIncludedResources($scope, $data = []);
    }
}

class TransformerWithWhitelist extends Transformer
{
    protected $relations = ['foo', 'bar'];
}

class TransformerWithWildcard extends Transformer
{
    protected $relations = ['*'];
}

class TransformerWithDefaultRelations extends Transformer
{
    protected $load = ['foo', 'bar' => TransformerWithoutWildcard::class];
}

class TransformerWithIncludeMethod extends Transformer
{
    protected $relations = ['foo'];

    public function includeFoo($data)
    {
        return $this->resource($data);
    }
}