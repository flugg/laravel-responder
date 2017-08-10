<?php

namespace Flugg\Responder\Tests\Unit\Serializers;

use Flugg\Responder\Serializers\ErrorSerializer;
use Flugg\Responder\Tests\TestCase;

/**
 * Unit tests for the [Flugg\Responder\Serializers\ErrorSerializer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorSerializerTest extends TestCase
{
    /**
     * The [ErrorSerializer] class being tested.
     *
     * @var \Flugg\Responder\Serializers\ErrorSerializer
     */
    protected $serializer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->serializer = new ErrorSerializer();
    }

    /**
     * Assert that the [format] method serializes the error data.
     */
    public function testFormatMethodSerializesErrorData()
    {
        $formattedData = $this->serializer->format($code = 'test_error', $message = 'A test error has occured.', $data = ['foo' => 1]);

        $this->assertEquals([
            'error' => [
                'code' => $code,
                'message' => $message,
                'foo' => 1,
            ],
        ], $formattedData);
    }
}