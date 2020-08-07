<?php

namespace Flugg\Responder\Tests\Unit\Adapters;

use Flugg\Responder\Adapters\IlluminatePaginatorAdapter as AdaptersIlluminatePaginatorAdapter;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Unit tests for the [Flugg\Responder\Adapters\IlluminatePaginatorAdapter] class.
 *
 * @see \Flugg\Responder\Adapters\IlluminatePaginatorAdapter
 */
class IlluminatePaginatorAdapterTest extends UnitTestCase
{
    /**
     * Mock of an Illuminate paginator.
     *
     * @var \Mockery\MockInterface|\Illuminate\Contracts\Pagination\LengthAwarePaginator
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

        $this->paginator = mock(LengthAwarePaginator::class);
        $this->adapter = new AdaptersIlluminatePaginatorAdapter($this->paginator);
    }

    /**
     * Assert that [currentPage] returns the current page.
     */
    public function testCurrentPageMethodReturnsCurrentPage()
    {
        $this->paginator->allows('currentPage')->andReturn($page = 2);

        $this->assertEquals($page, $this->adapter->currentPage());
    }

    /**
     * Assert that [lastPage] returns the next page.
     */
    public function testLastPageMethodReturnsLastPage()
    {
        $this->paginator->allows('lastPage')->andReturn($page = 3);

        $this->assertEquals($page, $this->adapter->lastPage());
    }

    /**
     * Assert that [total] returns the total count of items.
     */
    public function testTotalMethodReturnsTotalCount()
    {
        $this->paginator->allows('total')->andReturn($count = 30);

        $this->assertEquals($count, $this->adapter->total());
    }

    /**
     * Assert that [count] returns the current count of items.
     */
    public function testCountMethodReturnsCurrentCount()
    {
        $items = new Collection(range(0, 9));
        $this->paginator->allows('items')->andReturn($items);

        $this->assertEquals(count($items), $this->adapter->count());
    }

    /**
     * Assert that [perPage] returns the count of items per page.
     */
    public function testPerPageMethodReturnsCountPerPage()
    {
        $this->paginator->allows('perPage')->andReturn($count = 10);

        $this->assertEquals($count, $this->adapter->perPage());
    }

    /**
     * Assert that [url] returns the URL for the page.
     */
    public function testUrlMethodReturnsUrlForPage()
    {
        $url = 'test.com?page=';
        $this->paginator->allows('url')->andReturnUsing(function ($page) use ($url) {
            return $url . $page;
        });

        $this->assertEquals($url . ($page = 2), $this->adapter->url($page));
    }
}
