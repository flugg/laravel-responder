<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Pagination\IlluminatePaginatorAdapter;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * @var LengthAwarePaginator|MockInterface
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
     * Assert that the [currentPage] method retrieves the current page from the paginator.
     */
    public function testCurrentPageMethodReturnsCurrentPageFromPagintor()
    {
        $this->paginator->allows('currentPage')->andReturn($page = 5);

        $this->assertEquals($page, $this->adapter->currentPage());
    }
}
