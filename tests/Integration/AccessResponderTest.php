<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Facades\Responder as ResponderFacade;
use Flugg\Responder\Responder;

/**
 * This file is a collection of tests, testing that you can access the responder service
 * in multiple ways.
 *
 * @package flugger/laravel-responder
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
        $responder = app(Responder::class);

        // Assert...
        $this->assertInstanceOf(Responder::class, $responder);
    }

    /**
     * Test that you can access the responder service from the Laravel's IoC container.
     *
     * @test
     */
    public function youCanAccessThroughFacade()
    {
        // Arrange...
        $fruit = $this->createModel();
        $responder = $this->mockResponder();

        // Act...
        ResponderFacade::success($fruit, 200);

        // Assert...
        $responder->shouldHaveReceived('success')->with($fruit, 200);
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
        $this->assertInstanceOf(Responder::class, $responder);
    }

    /**
     * Test that you can access the responder service from the controller trait.
     *
     * @test
     */
    public function youCanAccessThroughControllerTrait()
    {
        // Arrange...
        $fruit = $this->createModel();
        $controller = $this->createTestController();
        $responder = $this->mockResponder();

        // Act...
        (new $controller)->successAction($fruit);

        // Assert...
        $responder->shouldHaveReceived('success')->with($fruit, null, []);
    }
}