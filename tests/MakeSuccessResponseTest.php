<?php

namespace Mangopixel\Responder\Tests;

use Illuminate\Http\JsonResponse;
use Mangopixel\Responder\Contracts\Responder;
use Mangopixel\Responder\Facades\ApiResponse;
use Mockery;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class MakeSuccessResponseTest extends TestCase
{
    /**
     *
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
     *
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
        (new $controller)->successMethod( $fruit );
    }

    /**
     *
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
}