<?php

namespace Flugg\Responder\Testing;

use Flugg\Responder\Contracts\Responder;

/**
 * Mixin class extending [Illuminate\Testing\TestResponse] with an [assertSuccess] method.
 */
class AssertSuccessMacro
{
    /**
     * An invokable function returning a macro callable.
     *
     * @return callable
     */
    public function __invoke(): callable
    {
        return function ($data) {
            $this->assertExactJson(
                app(Responder::class)->success($data)
                    ->respond($this->getStatusCode())
                    ->getData(true)
            );

            return $this;
        };
    }
}
