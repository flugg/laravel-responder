<?php

namespace Flugg\Responder\Testing;

use Flugg\Responder\Contracts\Responder;

/**
 * Mixin class extending [Illuminate\Testing\TestResponse] with an [assertError] method.
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
            $this->assertExactJson(
                app(Responder::class)->error($code, $message)
                    ->respond($this->getStatusCode())
                    ->getData(true)
            );

            return $this;
        };
    }
}
