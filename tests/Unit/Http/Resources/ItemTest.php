<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\Resources\Item] class.
 *
 * @see \Flugg\Responder\Http\Resources\Item
 */
class ItemTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Resources\Item
     */
    protected $item;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->item = new Item();
    }

    /**
     * Assert that [setKey] and [key] sets and gets resource key respectively.
     */
    public function testSetAndGetKey()
    {
        $result = $this->item->setKey($key = 'foo');

        $this->assertSame($this->item, $result);
        $this->assertEquals($key, $this->item->key());
    }

    /**
     * Assert that [setData] and [data] sets and gets resource data respectively.
     */
    public function testSetAndGetData()
    {
        $result = $this->item->setData($data = ['foo' => 123]);

        $this->assertSame($this->item, $result);
        $this->assertEquals($data, $this->item->data());
    }

    /**
     * Assert that [setRelations] and [relations] sets and gets nested resources respectively.
     */
    public function testSetAndGetRelations()
    {
        $this->item->setRelations($relations = ['foo' => new Item(), 'bar' => new Collection()]);

        $this->assertEquals($relations, $this->item->relations());
    }

    /**
     * Assert that the constructor sets data and resource key.
     */
    public function testInitializePropertiesInConstructor()
    {
        $resource = new Item($data = ['foo' => 123], $key = 'foo', $relations = ['foo' => new Item()]);

        $this->assertEquals($data, $resource->data());
        $this->assertEquals($key, $resource->key());
        $this->assertEquals($relations, $resource->relations());
    }

    /**
     * Assert that [toArray] returns the data as an array.
     */
    public function testToArrayMethodReturnsData()
    {
        $this->item->setData($data = ['foo' => 123]);

        $this->assertEquals($data, $this->item->toArray());
    }

    /**
     * Assert that you can fetch data from the resource using array access methods.
     */
    public function testArrayAccessMethodsAccessesData()
    {
        $this->item->setData(['foo' => 123]);

        $this->assertTrue(isset($this->item['foo']));
        $this->assertFalse(isset($this->item['bar']));
        $this->assertEquals(123, $this->item['foo']);

        unset($this->item['foo']);
        $this->item['bar'] = 456;

        $this->assertFalse(isset($this->item['foo']));
        $this->assertTrue(isset($this->item['bar']));
        $this->assertEquals(456, $this->item['bar']);
    }
}
