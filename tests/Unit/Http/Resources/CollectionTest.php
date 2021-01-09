<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Collection] class.
 *
 * @see \Flugg\Responder\Http\Resources\Collection
 */
class CollectionTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Resources\Collection
     */
    protected $collection;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new Collection();
    }

    /**
     * Assert that the constructor sets data and resource key.
     */
    public function testInitializePropertiesInConstructor()
    {
        $collection = new Collection($items = ['foo' => 1], $key = 'foo');

        $this->assertSame($items, $collection->items());
        $this->assertSame($key, $collection->key());
    }

    /**
     * Assert that [setKey] and [key] sets and gets resource key respectively.
     */
    public function testSetAndGetKey()
    {
        $result = $this->collection->setKey($key = 'foo');

        $this->assertSame($this->collection, $result);
        $this->assertSame($key, $this->collection->key());
    }

    /**
     * Assert that [setItems] and [items] sets and gets colletion items respectively.
     */
    public function testSetAndGetItems()
    {
        $result = $this->collection->setItems($items = [new Item(), new Item()]);

        $this->assertSame($this->collection, $result);
        $this->assertSame($items, $this->collection->items());
    }

    /**
     * Assert that [toArray] returns the data as an array.
     */
    public function testToArrayMethodReturnsData()
    {
        $this->collection->setItems([$item1 = new Item(), $item2 = new Item()]);

        $this->assertSame([$item1->toArray(), $item2->toArray()], $this->collection->toArray());
    }
}
