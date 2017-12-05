<?php

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Contracts\Transformer;

if (! function_exists('responder')) {

    /**
     * A helper method to resolve the responder service out of the service container.
     *
     * @return \Flugg\Responder\Contracts\Responder
     */
    function responder(): Responder
    {
        return app(Responder::class);
    }
}

if (! function_exists('transform')) {

    /**
     * A helper method to transform data without serializing.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|string|null $transformer
     * @param  string[]                                                       $with
     * @param  string[]                                                       $without
     * @return array|null
     */
    function transform($data = null, $transformer = null, array $with = [], array $without = []): ?array
    {
        return app(Transformer::class)->transform($data, $transformer, $with, $without);
    }
}
