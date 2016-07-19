<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Facades\Responder as ResponderFacade;
use Flugg\Responder\Responder;

/**
 * This file is a collection of tests, testing that you can access the responder service
 * in multiple ways.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class AccessResponderTest extends TestCase
{
    /**
     * Test that you can resolve the responder service from the Laravel's IoC container.
     *
     * @test
     */
    public function youCanResolveFromServiceContainer()
    {
        // Arrange...
        $responder = app( ResponderContract::class );

        // Assert...
        $this->assertInstanceOf( Responder::class, $responder );
    }

    /**
     * Test that you can access the responder service from the Laravel's IoC container.
     *
     * @test
     */
    public function youCanAccessThroughFacade()
    {
        // Arrange...
        $fruit = $this->createTestModel();
        $responder = $this->mockResponder();

        // Assert...
        $responder->shouldReceive( 'success' )->with( $fruit, 200 )->once();

        // Act...
        ResponderFacade::success( $fruit, 200 );
    }

    /**
     * Test that you can access the responder service from the helper methid.
     *
     * @test
     */
    public function youCanAccessThroughHelperMethod()
    {
        // Arrange...
        $responder = responder();

        // Assert...
        $this->assertInstanceOf( Responder::class, $responder );
    }

    /**
     * Test that you can access the responder service from the controller trait.
     *
     * @test
     */
    public function youCanAccessThroughControllerTrait()
    {
        // Arrange...
        $fruit = $this->createTestModel();
        $controller = $this->createTestController();
        $responder = $this->mockResponder();

        // Assert...
        $responder->shouldReceive( 'success' )->with( $fruit, 200, [ ] )->once();

        // Act...
        ( new $controller )->successAction( $fruit );
    }
}