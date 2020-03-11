<?php

namespace Flugg\Responder\Testing;

use Flugg\Responder\Contracts\Responder;

/**
 * Mixin class extending the TestResponse class with an [assertError] method.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class AssertErrorMacro
{
    /**
     * An invokable function returning a macro callable.
     *
     * @return callable
     */
    public function __invoke(): callable
    {
        return function ($code, $message = null) {
            $this->assertExactJson(app(Responder::class)->error($code, $message)->respond($this->getStatusCode())->getData(true));

            return $this;
        };
    }
}
