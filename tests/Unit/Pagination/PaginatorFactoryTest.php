<?php

namespace Flugg\Responder\Tests\Unit\Pagination;

use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Pagination\PaginatorFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class PaginatorFactoryTest extends TestCase
{
    /**
     * Assert that the [make] method creates a paginator adapter from a [LengthAwarePaginator].
     */
    public function testMakeMethodShouldCreatePaginatorAdapters(): void
    {
        $factory = new PaginatorFactory($parameters = ['foo' => 1]);
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $paginator->shouldReceive('appends')->andReturnSelf();

        $result = $factory->make($paginator);

        $this->assertInstanceOf(PaginatorInterface::class, $result);
        $paginator->shouldHaveReceived('appends')->with($parameters)->once();
    }

    /**
     * Assert that the [make] method creates a [Cursor] object from a [CursorPaginator].
     */
    public function testMakeMethodCreatesAFractalCursor(): void
    {
        $factory = new PaginatorFactory($parameters = ['foo' => 1]);
        $paginator = Mockery::mock(CursorPaginator::class);
        $paginator->shouldReceive('cursor')->andReturn($current = 2);
        $paginator->shouldReceive('previous')->andReturn($previous = 1);
        $paginator->shouldReceive('next')->andReturn($next = 3);
        $paginator->shouldReceive('get')->andReturn($collection = Collection::make([1, 2, 3]));

        $result = $factory->makeCursor($paginator);

        $this->assertInstanceOf(CursorInterface::class, $result);
        $this->assertEquals($current, $result->getCurrent());
        $this->assertEquals($previous, $result->getPrev());
        $this->assertEquals($next, $result->getNext());
        $this->assertEquals(3, $result->getCount());
    }
}