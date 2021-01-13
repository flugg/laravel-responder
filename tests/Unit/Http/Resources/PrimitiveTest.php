<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Http\Resources\Primitive;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Primitive] class.
 *
 * @see \Flugg\Responder\Http\Resources\Primitive
 */
class PrimitiveTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Resources\Primitive
     */
    protected $primitive;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->primitive = new Primitive;
    }

    /**
     * Assert that the constructor sets data and resource key.
     */
    public function testInitializePropertiesInConstructor()
    {
        foreach ([true, 1.0, 1, 'foo'] as $data) {
            $resource = new Primitive($data, $key = 'bar');

            $this->assertSame($data, $resource->data());
            $this->assertSame($key, $resource->key());
        }
    }

    /**
     * Assert that [setKey] and [key] sets and gets resource key respectively.
     */
    public function testSetAndGetKey()
    {
        $result = $this->primitive->setKey($key = 'foo');

        $this->assertSame($this->primitive, $result);
        $this->assertSame($key, $this->primitive->key());
    }

    /**
     * Assert that [setData] and [data] sets and gets resource data respectively.
     */
    public function testSetAndGetData()
    {
        $result = $this->primitive->setData($data = ['foo' => 1]);

        $this->assertSame($this->primitive, $result);
        $this->assertSame($data, $this->primitive->data());
    }
}
