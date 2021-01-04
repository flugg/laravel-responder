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
     * Assert that [normalize] normalizes arrayable to a success response value object.
     */
    public function testNormalizeMethodNormalizesArrayable()
    {
        $arrayable = mock(Arrayable::class);
        $arrayable->allows('toArray')->andReturns($data = ['foo' => 123]);
        $normalizer = new ArrayableNormalizer($arrayable);

        $result = $normalizer->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertEquals(200, $result->status());
        $this->assertSame($data, $result->resource()->toArray());
        $this->assertNull($result->resource()->key());
    }
}
