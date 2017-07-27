<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Serializer Class Paths
    |--------------------------------------------------------------------------
    |
    | The full class path to the serializer classes you want to use for both
    | success- and error responses. The success serializer must implement
    | Fractal's serializer. You can override these for every response.
    |
    */

    'serializers' => [
        'success' => Flugg\Responder\Serializers\SuccessSerializer::class,
        'error' => \Flugg\Responder\Serializers\ErrorSerializer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Decorators
    |--------------------------------------------------------------------------
    |
    | Response decorators are used to decorate both your success- and error
    | responses. A decorator can be disabled by removing it from the list
    | below. You may additionally add your own decorators to the list.
    |
    */

    'decorators' => [
        \Flugg\Responder\Http\Responses\Decorators\StatusCodeDecorator::class,
        \Flugg\Responder\Http\Responses\Decorators\SuccessFlagDecorator::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoload Relationships With Query String
    |--------------------------------------------------------------------------
    |
    | The package can automatically load relationships from the query string
    | and will look for a query string parameter with the name configured
    | below. You can set the value to null to disable the autoloading.
    |
    */

    'load_relations_parameter' => 'with',

    /*
    |--------------------------------------------------------------------------
    | Filter Fields With Query String
    |--------------------------------------------------------------------------
    |
    | The package can automatically filter the fields of transformed data
    | from a query string parameter configured below. The technique is
    | also known as sparse fieldsets. Set it to null to disable it.
    |
    */

    'filter_fields_parameter' => 'only',

    /*
    |--------------------------------------------------------------------------
    | Recursion Limit
    |--------------------------------------------------------------------------
    |
    | When transforming data, you may be including relations recursively.
    | By setting the value below, you can limit the amount of times it
    | should include relationships recursively. Five might be good.
    |
    */

    'recursion_limit' => 5,

];