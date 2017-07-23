<?php

namespace Flugg\Responder\Tests\Unit\Resources;

use Flugg\Responder\Pagination\CursorFactory;
use Flugg\Responder\Resources\DataNormalizer;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformers\TransformerManager;
use Flugg\Responder\Transformers\TransformerResolver;
use League\Fractal\Resource\NullResource;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Resources\ResourceFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResourceFactoryTest extends TestCase
{
    /**
     * Mock of a resource data normalizer class.
     *
     * @var \Mockery\MockInterface
     */
    protected $normalizer;

    /**
     * Mock of a transformer resolver class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformerResolver;

    /**
     * The resource factory being tested.
     *
     * @var \Flugg\Responder\Resources\ResourceFactory
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

        $this->normalizer = Mockery::mock(DataNormalizer::class);
        $this->transformerResolver = Mockery::mock(TransformerResolver::class);
        $this->factory = new ResourceFactory($this->normalizer, $this->transformerResolver);
    }

    /**
     *
     */
    public function testMakeMethodMakesANullResourceWithNoArguments()
    {
        $this->normalizer->shouldReceive('normalize')->andReturn(null);

        $resource = $this->factory->make();

        $this->assertInstanceOf(NullResource::class, $resource);
        $this->normalizer->shouldHaveReceived('normalize')->with(null);
    }
}