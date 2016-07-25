<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Facades\Responder;
use Illuminate\Http\JsonResponse;

/**
 * This file is a collection of tests, testing that you can generate error responses.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class MakeErrorResponseTest extends TestCase
{
    /**
     * Test that you can generate error responses using the responder service.
     *
     * @test
     */
    public function youCanMakeErrorResponses()
    {
        // Act...
        $response = $this->responder->error( 'test_error', 400, 'Test error.' );

        // Assert...
        $this->assertInstanceOf( JsonResponse::class, $response );
        $this->assertEquals( $response->getStatusCode(), 400 );
        $this->assertEquals( $response->getData( true ), [
            'status' => 400,
            'success' => false,
            'error' => [
                'code' => 'test_error',
                'message' => 'Test error.'
            ]
        ] );
    }

    /**
     * Test that you can generate error responses using the helper method.
     *
     * @test
     */
    public function youCanMakeErrorResponsesUsingHelperMethod()
    {
        // Arrange...
        $responder = $this->mockResponder();

        // Expect...
        $responder->shouldReceive( 'error' )->with( 'test_error', 400, 'Test error.' )->once();

        // Act...
        responder()->error( 'test_error', 400, 'Test error.' );
    }

    /**
     * Test that you can generate error responses using the facade.
     *
     * @test
     */
    public function youCanMakeErrorResponsesUsingFacade()
    {
        // Arrange...
        $responder = $this->mockResponder();

        // Expect...
        $responder->shouldReceive( 'error' )->with( 'test_error', 400, 'Test error.' )->once();

        // Act...
        Responder::error( 'test_error', 400, 'Test error.' );
    }

    /**
     * Test that you can generate error responses using the RespondsWithJson trait.
     *
     * @test
     */
    public function youCanMakeErrorResponsesUsingTrait()
    {
        // Arrange...
        $controller = $this->createTestController();
        $responder = $this->mockResponder();

        // Expect...
        $responder->shouldReceive( 'error' )->with( 'test_error', 400, 'Test error.' )->once();

        // Act...
        ( new $controller )->errorAction();
    }

    /**
     * Test that it uses error messages from the package language file based on error code.
     *
     * @test
     */
    public function youCanUseLangFilesForErrorMessages()
    {
        // Arrange...
        $translator = $this->mockTranslator();

        // Expect...
        $translator->shouldReceive( 'has' )->with( 'errors.test_error' )->once()->andReturn( true );
        $translator->shouldReceive( 'trans' )->with( 'errors.test_error' )->once()->andReturn( 'Test error.' );

        // Act...
        $response = $this->responder->error( 'test_error', 400 );

        // Assert...
        $this->assertEquals( $response->getData( true )[ 'error' ][ 'message' ], 'Test error.' );
    }
}