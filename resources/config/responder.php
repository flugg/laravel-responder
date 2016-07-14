<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Serializer Class Path
    |--------------------------------------------------------------------------
    |
    | The full class path to the serializer class you would like the package
    | to use when generating successful JSON responses. You may change it
    | to one of Fractal's serializers or create a custom one yourself.
    |
    */

    'serializer' => Flugg\Responder\Serializers\ApiSerializer::class,

    /*
    |--------------------------------------------------------------------------
    | Include Status Code
    |--------------------------------------------------------------------------
    |
    | Wether or not you want to include status codes in your JSON responses.
    | The status code is then be prepended to both your success and error
    | responses. This happens after serializing for success responses.
    |
    */

    'status_code' => true

];