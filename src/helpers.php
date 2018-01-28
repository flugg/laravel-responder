<?php

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Transformation;
use Flugg\Responder\TransformBuilder;

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

if (! function_exists('transformation')) {

    /**
     * A helper method to transform data without serializing.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|string|null $transformer
     * @return \Flugg\Responder\TransformBuilder
     */
    function transformation($data = null, $transformer = null): TransformBuilder
    {
        return app(Transformation::class)->make($data, $transformer);
    }
}
