<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Response Decorators
    |--------------------------------------------------------------------------
    |
    | Wether or not you want to include status codes in your JSON responses.
    | If true the status code is prepended to both your success and error
    | responses. This takes place right after your data is serialized.
    |
    */

    'decorators' => [
        \Flugg\Responder\Http\Responses\Decorators\StatusCodeDecorator::class,
        \Flugg\Responder\Http\Responses\Decorators\SuccessFlagDecorator::class,
    ],

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

    'serializers' => [
        'success' => Flugg\Responder\Serializers\SuccessSerializer::class,
        'error' => \Flugg\Responder\Serializers\ErrorSerializer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoload Relations From Query String
    |--------------------------------------------------------------------------
    |
    | The responder will automatically parse and load relations from a query
    | string parameter if the value below is a string value. If you don't
    | want the package to auto load relations, you can set it to null.
    |
    */

    'load_relations_parameter' => 'with',

    /*
    |--------------------------------------------------------------------------
    | Autoload Relations From Query String
    |--------------------------------------------------------------------------
    |
    | The responder will automatically parse and load relations from a query
    | string parameter if the value below is a string value. If you don't
    | want the package to auto load relations, you can set it to null.
    |
    */

    'eager_load_relations' => true,

    /*
    |--------------------------------------------------------------------------
    | Load Relations From Parameter
    |--------------------------------------------------------------------------
    |
    | The responder will automatically parse and load relations from a query
    | string parameter if the value below is a string value. If you don't
    | want the package to auto load relations, you can set it to null.
    |
    */

    'recursion_limit' => 10,

];