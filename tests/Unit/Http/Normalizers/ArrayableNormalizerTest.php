<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\ArrayableNormalizer;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Unit tests for the [Flugg\Responder\Http\Normalizers\ArrayableNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\ArrayableNormalizer
 */
class ArrayableNormalizerTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Normalizers\ArrayableNormalizer
     */
    protected $normalizer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new ArrayableNormalizer;
    }

    /**
     * Assert that [normalize] normalizes arrayable to a success response value object.
     */
    public function testNormalizeMethodNormalizesArrayable()
    {
        $arrayable = mock(Arrayable::class);
        $arrayable->allows('toArray')->andReturns($data = ['foo' => 123]);

        $result = $this->normalizer->normalize($arrayable);

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame($data, $result->data());
    }
}
