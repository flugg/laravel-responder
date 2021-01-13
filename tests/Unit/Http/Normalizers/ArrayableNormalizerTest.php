<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\ArrayableNormalizer;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Unit tests for the [ArrayableNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\ArrayableNormalizer
 */
class ArrayableNormalizerTest extends UnitTestCase
{
    /**
     * Assert that [normalize] normalizes arrayable to a success response.
     */
    public function testNormalizeMethodNormalizesArrayable()
    {
        $arrayable = $this->mock(Arrayable::class);
        $arrayable->toArray()->willReturn($data = ['foo' => 1]);

        $result = (new ArrayableNormalizer($arrayable->reveal()))->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame(200, $result->status());
        $this->assertInstanceOf(Item::class, $result->resource());
        $this->assertSame($data, $result->resource()->data());
        $this->assertNull($result->resource()->key());
    }
}
