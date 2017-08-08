<?php

namespace Flugg\Responder\Tests\Unit\Transformers;

use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformers\Transformer;
use Mockery;

/**
 * Unit tests for the abstract [Flugg\Responder\Transformers\Transformer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformerTest
{
    /**
     * The [Transformer] class being tested.
     *
     * @var \Flugg\Responder\Transformers\Transformer
     */
    protected $transformer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->transformer = Mockery::mock(Transformer::class);
    }
}