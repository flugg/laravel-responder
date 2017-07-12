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
     * The error serializer being tested.
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
     *
     */
    public function testFormatMethodSerializesErrorData()
    {
        [$code, $message, $data] = ['test_error', 'A test error has occured.', ['foo' => 1]];

        $formattedData = $this->serializer->format($code, $message, $data);

        $this->assertEquals([
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ],
        ], $formattedData);
    }
}