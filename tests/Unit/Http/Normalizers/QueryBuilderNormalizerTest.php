<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Unit tests for the [Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer
 */
class QueryBuilderNormalizerTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer
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

        $this->normalizer = new QueryBuilderNormalizer;
    }

    /**
     * Assert that [normalize] normalizes query builder to a success response value object.
     */
    public function testNormalizeMethodNormalizesQueryBuilder()
    {
        $queryBuilder = mock(Builder::class);
        $queryBuilder->allows('get')->andReturns(Collection::make($data = [1, 2, 3]));

        $result = $this->normalizer->normalize($queryBuilder);

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame($data, $result->data());
    }
}
