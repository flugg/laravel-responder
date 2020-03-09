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
     * The service class being tested.
     *
     * @var AdapterFactory
     */
    protected $factory;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->factory = new AdapterFactory();
    }

    /**
     * Assert that you get null back when calling the [makePaginator] method when no adapter is set.
     */
    public function testMakePaginatorMethodReturnsNullIfNoAdapterIsFound()
    {
        $adapter = $this->factory->makePaginator('foo');

        $this->assertNull($adapter);
    }

    /**
     * Assert that you get null back when calling the [makePaginator] method when no adapter is set.
     */
    public function testMakePaginatorMethodReturnsAdapter()
    {
        $paginator = mock();
        $paginatorAdapter = mock(Paginator::class);
        $this->factory::$adapters = [
            Paginator::class => [get_class($paginator) => get_class($paginatorAdapter)],
        ];

        $adapter = $this->factory->makePaginator($paginator);

        $this->assertInstanceOf(get_class($paginatorAdapter), $adapter);
    }

    /**
     * Assert that you get null back when calling the [makeCursorPaginator] method when no adapter is set.
     */
    public function testMakeCursorPaginatorMethodReturnsNullIfNoAdapterIsFound()
    {
        $adapter = $this->factory->makeCursorPaginator('foo');

        $this->assertNull($adapter);
    }

    /**
     * Assert that you get null back when calling the [makeCursorPaginator] method when no adapter is set.
     */
    public function testMakeCursorPaginatorMethodReturnsAdapter()
    {
        $cursorPaginator = mock();
        $cursorPaginatorAdapter = mock(CursorPaginator::class);
        $this->factory::$adapters = [
            CursorPaginator::class => [get_class($cursorPaginator) => get_class($cursorPaginatorAdapter)],
        ];

        $adapter = $this->factory->makeCursorPaginator($cursorPaginator);

        $this->assertInstanceOf(get_class($cursorPaginatorAdapter), $adapter);
    }

    /**
     * Assert that you get null back when calling the [makeValidator] method when no adapter is set.
     */
    public function testMakeValidatorMethodReturnsNullIfNoAdapterIsFound()
    {
        $adapter = $this->factory->makeValidator('foo');

        $this->assertNull($adapter);
    }

    /**
     * Assert that you get null back when calling the [makeValidator] method when no adapter is set.
     */
    public function testMakeValidatorMethodReturnsAdapter()
    {
        $validator = mock();
        $validatorAdapter = mock(Validator::class);
        $this->factory::$adapters = [
            Validator::class => [get_class($validator) => get_class($validatorAdapter)],
        ];

        $adapter = $this->factory->makeValidator($validator);

        $this->assertInstanceOf(get_class($validatorAdapter), $adapter);
    }
}
