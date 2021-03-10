<?php

namespace Flugg\Responder\Tests\Unit\Adapters;

use Flugg\Responder\Adapters\IlluminatePaginatorAdapter;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Unit tests for the [IlluminatePaginatorAdapter] class.
 *
 * @see \Flugg\Responder\Adapters\IlluminatePaginatorAdapter
 */
class IlluminatePaginatorAdapterTest extends UnitTestCase
{
    /**
     * Mock of an [\Illuminate\Contracts\Pagination\LengthAwarePaginator] class.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $paginator;

    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Adapters\IlluminatePaginatorAdapter
     */
    protected $adapter;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->paginator = $this->mock(LengthAwarePaginator::class);
        $this->adapter = new IlluminatePaginatorAdapter($this->paginator->reveal());
    }

    /**
     * Assert that [currentPage] returns the current page.
     */
    public function testCurrentPageMethodReturnsCurrentPage()
    {
        $this->paginator->currentPage()->willReturn($page = 2);

        $this->assertSame($page, $this->adapter->currentPage());
    }

    /**
     * Assert that [lastPage] returns the next page.
     */
    public function testLastPageMethodReturnsLastPage()
    {
        $this->paginator->lastPage()->willReturn($page = 3);

        $this->assertSame($page, $this->adapter->lastPage());
    }

    /**
     * Assert that [total] returns the total count of items.
     */
    public function testTotalMethodReturnsTotalCount()
    {
        $this->paginator->total()->willReturn($count = 30);

        $this->assertSame($count, $this->adapter->total());
    }

    /**
     * Assert that [count] returns the current count of items.
     */
    public function testCountMethodReturnsCurrentCount()
    {
        $this->paginator->items()->willReturn($items = Collection::make(range(0, 9)));

        $this->assertSame(count($items), $this->adapter->count());
    }

    /**
     * Assert that [perPage] returns the count of items per page.
     */
    public function testPerPageMethodReturnsCountPerPage()
    {
        $this->paginator->perPage()->willReturn($count = 10);

        $this->assertSame($count, $this->adapter->perPage());
    }

    /**
     * Assert that [url] returns the URL for the given page.
     */
    public function testUrlMethodReturnsUrlForPage()
    {
        $this->paginator->url($page = 2)->willReturn($url = 'foo');

        $this->assertSame($url, $this->adapter->url($page));
    }
}
