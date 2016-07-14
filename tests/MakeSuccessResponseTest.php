<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Facades\ApiResponse;
use Illuminate\Http\JsonResponse;
use Mockery;

/**
 * This file is a collection of tests, testing that you can generate success responses.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class MakeSuccessResponseTest extends TestCase
{
    /**
     * Test that you can generate success responses using the responder service.
     *
     * @test
     */
    public function youCanMakeSuccessResponses()
    {
        // Arrange...
        $fruit = $this->createTestModel();

        // Act...
        $response = $this->responder->success( $fruit );

        // Assert...
        $this->assertInstanceOf( JsonResponse::class, $response );
        $this->assertEquals( $response->getStatusCode(), 200 );
        $this->assertEquals( $response->getData( true ), [
            'status' => 200,
            'success' => true,
            'data' => [
                'name' => 'Mango',
                'price' => 10,
                'isRotten' => false
            ]
        ] );
    }

    /**
     * Test that you can generate success responses using the facade.
     *
     * @test
     */
    public function youCanMakeSuccessResponsesUsingFacade()
    {
        // Arrange...
        $fruit = $this->createTestModel();

        $responder = Mockery::mock( Responder::class );
        $this->app->instance( Responder::class, $responder );

        // Assert...
        $responder->shouldReceive( 'success' )->with( $fruit, 200 )->once();

        // Act...
        ApiResponse::success( $fruit, 200 );
    }

    /**
     * Test that you can generate success responses using the helper method.
     *
     * @test
     */
    public function youCanMakeSuccessResponsesUsingHelperMethod()
    {
        // Arrange...
        $fruit = $this->createTestModel();

        // Assert...
        $responder->shouldReceive( 'success' )->with( $fruit, 200 )->once();

        // Act...
        responder()->success( $fruit, 200 );
    }

    /**
     * Test that you can generate success responses using the RespondsWithJson trait.
     *
     * @test
     */
    public function youCanMakeSuccessResponsesUsingTrait()
    {
        // Arrange...
        $fruit = $this->createTestModel();
        $controller = $this->createTestController();

        $responder = Mockery::mock( Responder::class );
        $this->app->instance( Responder::class, $responder );

        // Assert...
        $responder->shouldReceive( 'success' )->with( $fruit, 200 )->once();

        // Act...
        ( new $controller )->successAction( $fruit );
    }
}