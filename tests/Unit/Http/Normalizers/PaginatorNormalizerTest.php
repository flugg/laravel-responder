<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Adapters\IlluminatePaginatorAdapter;
use Flugg\Responder\Http\Normalizers\PaginatorNormalizer;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Unit tests for the [Flugg\Responder\Http\Normalizers\PaginatorNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\PaginatorNormalizer
 */
class PaginatorNormalizerTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Normalizers\PaginatorNormalizer
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

        $this->normalizer = new PaginatorNormalizer;
    }

    /**
     * Assert that [normalize] normalizes paginator to a success response value object.
     */
    public function testNormalizeMethodNormalizesPaginator()
    {
        $paginator = mock(LengthAwarePaginator::class);
        $paginator->allows('items')->andReturns($data = [1, 2, 3]);

        $result = $this->normalizer->normalize($paginator);

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertInstanceOf(IlluminatePaginatorAdapter::class, $result->paginator());
        $this->assertSame($data, $result->data());
    }
}
