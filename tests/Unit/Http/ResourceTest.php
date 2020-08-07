<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Http\Resource;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\Resource] class.
 *
 * @see \Flugg\Responder\Http\Resource
 */
class ResourceTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Resource
     */
    protected $resource;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->resource = new Resource([]);
    }

    /**
     * Assert that [setData] and [data] sets and gets resource data respectively.
     */
    public function testSetAndGetData()
    {
        $result = $this->resource->setData($data = ['foo' => 123]);

        $this->assertSame($this->resource, $result);
        $this->assertEquals($data, $this->resource->data());
    }

    /**
     * Assert that [setKey] and [key] sets and gets resource key respectively.
     */
    public function testSetAndGetKey()
    {
        $result = $this->resource->setKey($key = 'foo');

        $this->assertSame($this->resource, $result);
        $this->assertEquals($key, $this->resource->key());
    }

    /**
     * Assert that [setRelations] and [relations] sets and gets nested resources respectively.
     */
    public function testSetAndGetRelations()
    {
        $this->resource->setRelations($relations = [new Resource([])]);

        $this->assertEquals($relations, $this->resource->relations());
    }

    /**
     * Assert that the constructor sets data, resource key and relations.
     */
    public function testInitializePropertiesInConstructor()
    {
        $resource = new Resource($data = ['foo' => 123], $key = 'foo', $relations = [new Resource([])]);

        $this->assertEquals($data, $resource->data());
        $this->assertEquals($key, $resource->key());
        $this->assertEquals($relations, $resource->relations());
    }
}
