<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Item] class.
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->item = new Item();
    }

    /**
     * Assert that the constructor sets data and resource key.
     */
    public function testInitializePropertiesInConstructor()
    {
        $resource = new Item($data = ['foo' => 1], $key = 'bar', $relations = ['baz' => new Item()]);

        $this->assertSame($data, $resource->data());
        $this->assertSame($key, $resource->key());
        $this->assertSame($relations, $resource->relations());
    }

    /**
     * Assert that [setKey] and [key] sets and gets resource key respectively.
     */
    public function testSetAndGetKey()
    {
        $result = $this->item->setKey($key = 'foo');

        $this->assertSame($this->item, $result);
        $this->assertSame($key, $this->item->key());
    }

    /**
     * Assert that [setData] and [data] sets and gets resource data respectively.
     */
    public function testSetAndGetData()
    {
        $result = $this->item->setData($data = ['foo' => 1]);

        $this->assertSame($this->item, $result);
        $this->assertSame($data, $this->item->data());
    }

    /**
     * Assert that [setRelations] and [relations] sets and gets nested resources respectively.
     */
    public function testSetAndGetRelations()
    {
        $this->item->setRelations($relations = ['foo' => new Item(), 'bar' => new Collection()]);

        $this->assertSame($relations, $this->item->relations());
    }

    /**
     * Assert that [toArray] returns the data as an array.
     */
    public function testToArrayMethodReturnsData()
    {
        $this->item->setData($data = ['foo' => 1]);

        $this->assertSame($data, $this->item->toArray());
    }

    /**
     * Assert that you can fetch data from the resource using array access methods.
     */
    public function testArrayAccessMethodsAccessesData()
    {
        $this->item->setData(['foo' => 1]);

        $this->assertTrue(isset($this->item['foo']));
        $this->assertFalse(isset($this->item['bar']));
        $this->assertSame(1, $this->item['foo']);

        unset($this->item['foo']);
        $this->item['bar'] = 2;

        $this->assertFalse(isset($this->item['foo']));
        $this->assertTrue(isset($this->item['bar']));
        $this->assertSame(2, $this->item['bar']);
    }
}
