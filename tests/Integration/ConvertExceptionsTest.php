<?php

namespace Flugg\Responder\Tests\Integration;

use Flugg\Responder\Exceptions\ConvertsExceptions;
use Flugg\Responder\Tests\IntegrationTestCase;

/**
 * Integration tests for testing conversion of exceptions to error responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ConvertExceptionsTest extends IntegrationTestCase
{
    /**
     * A mock of a trait for converting exceptions.
     *
     * @var MockInterface|ConvertsExceptions
     */
    protected $trait;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->trait = $this->getMockForTrait(ConvertsExceptions::class);
    }
}
