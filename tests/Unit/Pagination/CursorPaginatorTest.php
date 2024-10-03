<?php

namespace Flugg\Responder\Tests\Unit\Pagination;

use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Support\Collection;
use LogicException;

/**
 * Unit tests for the [Flugg\Responder\Pagination\PaginatorFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class CursorPaginatorTest extends TestCase
{
    /**
     * Assert that the [previous], [cursor] and [next] methods allow you to get information
     * about the cursor.
     */
    public function testYouCanGetCursorInformationFromPaginator(): void
    {
        $paginator = new CursorPaginator(null, $cursor = 2, $previousCursor = 1, $nextCursor = 3);

        $this->assertEquals(1, $paginator->previous());
        $this->assertEquals(2, $paginator->cursor());
        $this->assertEquals(3, $paginator->next());
    }

    /**
     * Assert that the [items] and [get] methods allow you to get data from paginator.
     */
    public function testYouCanGetDataFromPaginator(): void
    {
        $paginator = new CursorPaginator($data = ['foo', 'bar'], null, null, null);

        $this->assertEquals($data, $paginator->items());
        $this->assertEquals(Collection::make($data), $paginator->get());
    }

    /**
     * Assert that the [set] method allows you to get override the cursor data.
     */
    public function testSetMethodAllowsYouToOverrideData(): void
    {
        $paginator = new CursorPaginator(['foo', 'bar'], null, null, null);

        $result = $paginator->set($data = ['bar', 'baz']);

        $this->assertSame($paginator, $result);
        $this->assertEquals($data, $paginator->items());
    }

    /**
     * Assert that the [resolveCursor] method throws a [LogicException] exception if no
     * resolver has been set.
     */
    public function testResolveCursorMethodThrowsExceptionIfNoResolverIsFound(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Could not resolve cursor with the name [foo].');

        CursorPaginator::resolveCursor('foo');
    }

    /**
     * Assert that the [cursorResolver] sets a resolver for the [resolveCursor] method.
     */
    public function testYouCanSetACursorResolver(): void
    {
        CursorPaginator::cursorResolver($resolver = function ($cursor) {
            return $cursor;
        });

        $result = CursorPaginator::resolveCursor($cursor = 'foo');

        $this->assertSame($cursor, $result);
    }
}
