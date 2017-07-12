<?php

namespace Flugg\Responder\Tests\Unit\Pagination;

use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\PaginatorInterface;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Pagination\PaginatorFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class PaginatorFactoryTest extends TestCase
{
    /**
     *
     */
    public function testMakeMethodCreatesAFractalPaginatorAdapter()
    {
        $factory = new PaginatorFactory($parameters = ['foo' => 1]);
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $paginator->shouldReceive('appends')->andReturnSelf();

        $result = $factory->make($paginator);

        $this->assertInstanceOf(PaginatorInterface::class, $result);
        $paginator->shouldHaveReceived('appends')->with($parameters)->once();
    }
}