<?php

namespace Flugg\Responder\Tests;

use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

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
     * Test that you can change the status code using a second argument.
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
     * Test that you can add meta data using a third argument.
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
     * Test that you can omit the first parameter.
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
     * Test that you can omit the second parameter.
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

    /**
     * Test that you may pass in an Eloquent collection as the data.
     *
     * @test
     */
    public function youCanUseACollectionAsData()
    {
        // Arrange...
        $mango = $this->createTestModel();
        $apple = $this->createTestModel( [
            'name' => 'Apple',
            'price' => 5
        ] );

        $fruits = collect( [ $mango, $apple ] );

        // Act...
        $response = $this->responder->success( $fruits );

        // Assert...
        $this->assertEquals( $response->getData( true ), [
            'status' => 200,
            'success' => true,
            'data' => [
                [
                    'name' => 'Mango',
                    'price' => 10,
                    'isRotten' => false
                ],
                [
                    'name' => 'Apple',
                    'price' => 5,
                    'isRotten' => false
                ]
            ]
        ] );
    }

    /**
     * Test that you may pass in an Eloquent builder as the data.
     *
     * @test
     */
    public function youCanUseABuilderAsData()
    {
        // Arrange...
        $fruit = $this->createTestModel()->newQuery();

        // Act...
        $response = $this->responder->success( $fruit );

        // Assert...
        $this->assertEquals( $response->getData( true ), [
            'status' => 200,
            'success' => true,
            'data' => [
                [
                    'name' => 'Mango',
                    'price' => 10,
                    'isRotten' => false
                ]
            ]
        ] );
    }

    /**
     * Test that you may pass in a Laravel paginator as the data.
     *
     * @test
     */
    public function youCanUseAPaginatorAsData()
    {
        // Arrange...
        $fruit = $this->createTestModel()->newQuery()->paginate( 1 );

        // Act...
        $response = $this->responder->success( $fruit );

        // Assert...
        $this->assertEquals( $response->getData( true ), [
            'status' => 200,
            'success' => true,
            'data' => [
                [
                    'name' => 'Mango',
                    'price' => 10,
                    'isRotten' => false
                ]
            ],
            'pagination' => [
                'total' => 1,
                'count' => 1,
                'perPage' => 1,
                'currentPage' => 1,
                'totalPages' => 1
            ]
        ] );
    }

    /**
     * Test that you may pass in a Laravel paginator as the data.
     *
     * @test
     */
    public function youCanUseNullAsData()
    {
        // Act...
        $response = $this->responder->success( null );

        // Assert...
        $this->assertEquals( $response->getData( true ), [
            'status' => 200,
            'success' => true,
            'data' => null
        ] );
    }

    /**
     * Test that you may only pass in models that implement the transformable contract.
     *
     * @test
     */
    public function youCanOnlyUseTransformableModels()
    {
        // Arrange...
        $fruit = $this->createTestModelWithNoTransformer();

        // Expect...
        $this->expectException( InvalidArgumentException::class );

        // Act...
        $this->responder->success( $fruit );
    }

    /**
     * Test that you may use no transformer by returning null in the transformer method.
     *
     * @test
     */
    public function youCanUseModelsWithANullTransformer()
    {
        // Arrange...
        $fruit = $this->createTestModelWithNullTransformer();

        // Act...
        $response = $this->responder->success( $fruit );

        // Assert...
        $this->assertEquals( $response->getData( true ), [
            'status' => 200,
            'success' => true,
            'data' => $fruit->toArray()
        ] );
    }
}