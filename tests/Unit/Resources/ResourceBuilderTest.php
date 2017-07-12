<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Exceptions\InvalidSerializerException;
use Flugg\Responder\FractalTransformFactory;
use Flugg\Responder\Resources\ResourceBuilder;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Serializers\NullSerializer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\TransformBuilder;
use Flugg\Responder\Transformers\TransformerManager;
use League\Fractal\Resource\NullResource;
use League\Fractal\Serializer\JsonApiSerializer;
use Mockery;
use stdClass;

/**
 * Unit tests for the [Flugg\Responder\Resources\ResourceBuilderTest] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResourceBuilderTest extends TestCase
{
    /**
     * Mock of the resource factory.
     *
     * @var \Mockery\MockInterface
     */
    protected $factory;

    /**
     * Mock of the transformer manager.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformerManager;

    /**
     * The resource builder.
     *
     * @var \Flugg\Responder\Resources\ResourceBuilder
     */
    protected $builder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->factory = Mockery::mock(ResourceFactory::class);
        $this->transformerManager = Mockery::mock(TransformerManager::class);
        $this->builder = new ResourceBuilder($this->factory, $this->transformerManager);
    }
}