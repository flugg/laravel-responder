<?php

use Flugg\Responder\Http\Normalizers\ArrayableNormalizer;
use Flugg\Responder\Http\Normalizers\CollectionNormalizer;
use Flugg\Responder\Http\Normalizers\ModelNormalizer;
use Flugg\Responder\Http\Normalizers\PaginatorNormalizer;
use Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer;
use Flugg\Responder\Http\Normalizers\RelationNormalizer;
use Flugg\Responder\Http\Normalizers\ResourceNormalizer;

return [

    /*
    |--------------------------------------------------------------------------
    | Response Formatter
    |--------------------------------------------------------------------------
    |
    | The response formatter is used to format the structure of the response
    | data of both success- and error responses. You may override this on
    | a per-response basis or set to null to fully disable formatting.
    |
    */

    'formatter' => \Flugg\Responder\Http\Formatters\SimpleFormatter::class,

    /*
    |--------------------------------------------------------------------------
    | Response Decorators
    |--------------------------------------------------------------------------
    |
    | Response decorators are used to decorate both your success- and error
    | responses and are typically used for adding headers or editing the
    | final output. These are executed after the response formatters.
    |
    */

    'decorators' => [
        // \Flugg\Responder\Http\Decorators\PrettyPrintDecorator::class,
        // \Flugg\Responder\Http\Decorators\EscapeHtmlDecorator::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Normalizers
    |--------------------------------------------------------------------------
    |
    | Response normalizers are used to normalize data given when building a
    | success response. Feel free to extend the list below with your own
    | normalizer in order for the package to support more data types.
    |
    */

    'normalizers' => [
        \Illuminate\Support\Collection::class => CollectionNormalizer::class,
        \Illuminate\Database\Eloquent\Model::class => ModelNormalizer::class,
        \Illuminate\Database\Eloquent\Collection::class => CollectionNormalizer::class,
        \Illuminate\Database\Eloquent\Relations\Relation::class => RelationNormalizer::class,
        \Illuminate\Database\Eloquent\Builder::class => QueryBuilderNormalizer::class,
        \Illuminate\Database\Query\Builder::class => QueryBuilderNormalizer::class,
        \Illuminate\Http\Resources\Json\JsonResource::class => ResourceNormalizer::class,
        \Illuminate\Contracts\Pagination\LengthAwarePaginator::class => PaginatorNormalizer::class,
        \Illuminate\Contracts\Support\Arrayable::class => ArrayableNormalizer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    |
    | A map of exceptions that will be automatically converted to an error
    | response for JSON requests. Responses having a status code of 5xx
    | will be ignored when debug mode is on for ease of development.
    |
    */

    'exceptions' => [
        \Illuminate\Auth\AuthenticationException::class => [
            'code' => 'unauthenticated',
            'status' => 401,
        ],
        \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class => [
            'code' => 'unauthorized',
            'status' => 403,
        ],
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class => [
            'code' => 'page_not_found',
            'status' => 404,
        ],
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class => [
            'code' => 'method_not_allowed',
            'status' => 405,
        ],
        \Illuminate\Database\Eloquent\RelationNotFoundException::class => [
            'code' => 'relation_not_found',
            'status' => 422,
        ],
        \Illuminate\Validation\ValidationException::class => [
            'code' => 'validation_failed',
            'status' => 422,
        ],
        \Exception::class => [
            'code' => 'server_error',
            'status' => 500,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | A list of error codes mapped to error messages. Feel free to add your
    | own error codes and error messages to the list. Alternatively, you
    | may also use the error message registry class to register them.
    |
    */

    'error_messages' => [
        'unauthenticated' => 'You are not authenticated',
        'unauthorized' => 'You are not authorized',
        'page_not_found' => 'The requested page was not found',
        'method_not_allowed' => 'The method is not allowed for this request',
        'relation_not_found' => 'The requested relation was not found',
        'validation_failed' => 'The given data was invalid',
        'server_error' => 'Something went wrong',
    ],
];
