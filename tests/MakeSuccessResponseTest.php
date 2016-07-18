<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Facades\Responder;
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
        $responder = $this->mockResponder();

        // Assert...
        $responder->shouldReceive( 'success' )->with( $fruit, 200 )->once();

        // Act...
        Responder::success( $fruit, 200 );
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
        $responder = $this->mockResponder();

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
        $responder = $this->mockResponder();

        // Assert...
        $responder->shouldReceive( 'success' )->with( $fruit, 200 )->once();

        // Act...
        ( new $controller )->successAction( $fruit );
    }

    /**
     *
     *
     * @test
     */
    public function youCanPassInStatusCode()
    {
        // Arrange...
        $fruit = $this->createTestModel();

        // Act...
        $response = $this->responder->success( $fruit, 201 );

        // Assert...
        $this->assertEquals( $response->getStatusCode(), 201 );
    }

    /**
     *
     *
     * @test
     */
    public function youCanPassInMetaData()
    {
        // Arrange...
        $fruit = $this->createTestModel();
        $meta = [
            'foo' => 'bar'
        ];

        // Act...
        $response = $this->responder->success( $fruit, 200, $meta );

        // Assert...
        $this->assertEquals( $response->getStatusCode(), 200 );
        $this->assertContains( $meta, $response->getData( true ) );
    }

    /**
     *
     *
     * @test
     */
    public function youCanOmitData()
    {
        // Arrange...
        $meta = [
            'foo' => 'bar'
        ];

        // Act...
        $response = $this->responder->success( 200, $meta );

        // Assert...
        $this->assertEquals( $response->getStatusCode(), 200 );
        $this->assertContains( $meta, $response->getData( true ) );
    }

    /**
     *
     *
     * @test
     */
    public function youCanOmitStatusCode()
    {
        // Arrange...
        $fruit = $this->createTestModel();
        $meta = [
            'foo' => 'bar'
        ];

        // Act...
        $response = $this->responder->success( $fruit, $meta );

        // Assert...
        $this->assertEquals( $response->getStatusCode(), 200 );
        $this->assertContains( $meta, $response->getData( true ) );
    }
}