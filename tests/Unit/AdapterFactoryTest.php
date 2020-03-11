<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\AdapterFactory;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Flugg\Responder\AdapterFactory] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class AdapterFactoryTest extends UnitTestCase
{
    /**
     * Factory class being tested.
     *
     * @var AdapterFactory
     */
    protected $factory;

    /**
     * Assert that [makePaginator] returns null when no mapping exists.
     */
    public function testMakePaginatorMethodReturnsNull()
    {
        $this->factory = new AdapterFactory();

        $adapter = $this->factory->makePaginator('foo');

        $this->assertNull($adapter);
    }

    /**
     * Assert that [makePaginator] returns an adapter instance when a mapping exists.
     */
    public function testMakePaginatorMethodReturnsAdapter()
    {
        $paginator = mock();
        $paginatorAdapter = mock(Paginator::class);
        $this->factory = new AdapterFactory([
            Paginator::class => [get_class($paginator) => get_class($paginatorAdapter)],
        ]);

        $adapter = $this->factory->makePaginator($paginator);

        $this->assertInstanceOf(get_class($paginatorAdapter), $adapter);
    }

    /**
     * Assert that [makeCursorPaginator] returns null when no mapping exists.
     */
    public function testMakeCursorPaginatorMethodReturnsNull()
    {
        $this->factory = new AdapterFactory();

        $adapter = $this->factory->makeCursorPaginator('foo');

        $this->assertNull($adapter);
    }

    /**
     * Assert that [makeCursorPaginator] returns an adapter instance when a mapping exists.
     */
    public function testMakeCursorPaginatorMethodReturnsAdapter()
    {
        $cursorPaginator = mock();
        $cursorPaginatorAdapter = mock(CursorPaginator::class);
        $this->factory = new AdapterFactory([
            CursorPaginator::class => [get_class($cursorPaginator) => get_class($cursorPaginatorAdapter)],
        ]);

        $adapter = $this->factory->makeCursorPaginator($cursorPaginator);

        $this->assertInstanceOf(get_class($cursorPaginatorAdapter), $adapter);
    }

    /**
     * Assert that [makeValidator] returns null when no mapping exists.
     */
    public function testMakeValidatorMethodReturnsNull()
    {
        $this->factory = new AdapterFactory();

        $adapter = $this->factory->makeValidator('foo');

        $this->assertNull($adapter);
    }

    /**
     * Assert that [makeValidator] returns an adapter instance when a mapping exists.
     */
    public function testMakeValidatorMethodReturnsAdapter()
    {
        $validator = mock();
        $validatorAdapter = mock(Validator::class);
        $this->factory = new AdapterFactory([
            Validator::class => [get_class($validator) => get_class($validatorAdapter)],
        ]);

        $adapter = $this->factory->makeValidator($validator);

        $this->assertInstanceOf(get_class($validatorAdapter), $adapter);
    }
}
