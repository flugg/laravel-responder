<?php

namespace Flugg\Responder\Tests\Unit\Pagination;

use Flugg\Responder\Pagination\CursorFactory;
use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Support\Collection;
use League\Fractal\Pagination\CursorInterface;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Pagination\CursorFactoryTest] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class CursorFactoryTest extends TestCase
{
    /**
     *
     */
    public function testMakeMethodCreatesAFractalCursor()
    {
        $factory = new CursorFactory($parameters = ['foo' => 1]);
        $paginator = Mockery::mock(CursorPaginator::class);
        $paginator->shouldReceive('appends')->andReturnSelf();
        $paginator->shouldReceive('cursor')->andReturn($current = 2);
        $paginator->shouldReceive('previousCursor')->andReturn($previous = 1);
        $paginator->shouldReceive('nextCursor')->andReturn($next = 3);
        $paginator->shouldReceive('getCollection')->andReturn($collection = new Collection([1, 2, 3]));

        $result = $factory->make($paginator);

        $this->assertInstanceOf(CursorInterface::class, $result);
        $this->assertEquals($current, $result->getCurrent());
        $this->assertEquals($previous, $result->getPrev());
        $this->assertEquals($next, $result->getNext());
        $this->assertEquals(3, $result->getCount());
        $paginator->shouldHaveReceived('appends')->with($parameters)->once();
    }
}