<?php

namespace Flugg\Responder\Tests\Unit\Pagination;

use Flugg\Responder\Pagination\IlluminatePaginatorAdapter;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery\MockInterface;


/**
 * Unit tests for the [Flugg\Responder\Pagination\IlluminatePaginatorAdapter] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class IlluminatePaginatorAdapterTest extends UnitTestCase
{
    /**
     * A mock of an Illuminate paginator.
     *
     * @var MockInterface|LengthAwarePaginator
     */
    protected $paginator;

    /**
     * The adapter class being tested.
     *
     * @var IlluminatePaginatorAdapter
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
        $this->adapter = new IlluminatePaginatorAdapter($this->paginator);
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
