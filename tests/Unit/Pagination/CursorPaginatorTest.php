<?php

namespace Flugg\Responder\Tests\Unit\Pagination;

use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use LogicException;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Pagination\PaginatorFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class CursorPaginatorTest extends TestCase
{
    /**
     *
     */
    public function testYouCanGetCursorInformationFromPaginator()
    {
        $paginator = new CursorPaginator(null, $cursor = 2, $previousCursor = 1, $nextCursor = 3);

        $this->assertEquals(1, $paginator->previous());
        $this->assertEquals(2, $paginator->cursor());
        $this->assertEquals(3, $paginator->next());
    }

    /**
     *
     */
    public function testYouCanGetDataFromPaginator()
    {
        $paginator = new CursorPaginator($data = ['foo', 'bar'], null, null, null);

        $this->assertEquals($data, $paginator->items());
        $this->assertEquals(Collection::make($data), $paginator->get());
    }

    /**
     *
     */
    public function testSetMethodAllowsYouToOverrideData()
    {
        $paginator = new CursorPaginator(['foo', 'bar'], null, null, null);

        $result = $paginator->set($data = ['bar', 'baz']);

        $this->assertSame($paginator, $result);
        $this->assertEquals($data, $paginator->items());
    }

    /**
     *
     */
    public function testResolveCursorMethodThrowsExceptionIfNoResolverIsFound()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Could not resolve cursor with the name [foo].');

        CursorPaginator::resolveCursor('foo');
    }

    /**
     *
     */
    public function testYouCanSetACursorResolver()
    {
        CursorPaginator::cursorResolver($resolver = function($cursor) {
            return $cursor;
        });

        $result = CursorPaginator::resolveCursor($cursor = 'foo');

        $this->assertSame($cursor, $result);
    }
}