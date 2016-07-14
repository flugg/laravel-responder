<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Facades\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Translation\Translator;
use Mockery;

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
     * Test that you can generate error responses using the facade.
     *
     * @test
     */
    public function youCanMakeErrorResponsesUsingFacade()
    {
        // Arrange...
        $responder = Mockery::mock( Responder::class );
        $this->app->instance( Responder::class, $responder );

        // Expect...
        $responder->shouldReceive( 'error' )->with( 'test_error', 400, 'Test error.' )->once();

        // Act...
        ApiResponse::error( 'test_error', 400, 'Test error.' );
    }

    /**
     * Test that it uses error messages from the package language file based on error code.
     *
     * @test
     */
    public function itShouldUseLangFilesForErrorMessages()
    {
        // Arrange...
        $this->app->loadDeferredProvider( 'translator' );
        $translator = Mockery::mock( Translator::class );
        $this->app->instance( 'translator', $translator );

        // Expect...
        $translator->shouldReceive( 'trans' )->with( 'errors.test_error' )->once()->andReturn( 'Test error.' );

        // Act...
        $response = $this->responder->error( 'test_error', 400 );

        // Assert...
        $this->assertEquals( $response->getData( true )[ 'error' ][ 'message' ], 'Test error.' );
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

        $responder = Mockery::mock( Responder::class );
        $this->app->instance( Responder::class, $responder );

        // Expect...
        $responder->shouldReceive( 'error' )->with( 'test_error', 400, 'Test error.' )->once();

        // Act...
        ( new $controller )->errorAction();
    }
}