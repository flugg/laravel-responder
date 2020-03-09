<?php

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Pagination\IlluminatePaginatorAdapter;
use Flugg\Responder\Validation\IlluminateValidatorAdapter;

return [
    /*
    |--------------------------------------------------------------------------
    | Response Formatter
    |--------------------------------------------------------------------------
    |
    | Response decorators are used to decorate both your success- and error
    | responses. A decorator can be disabled by removing it from the list
    | below. You may additionally add your own decorators to the list.
    |
    */

    'formatter' => \Flugg\Responder\Http\Formatters\SimpleFormatter::class,

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
        // \Flugg\Responder\Http\Decorators\PrettyPrintDecorator::class,
        // \Flugg\Responder\Http\Decorators\EscapeHtmlDecorator::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Adapters
    |--------------------------------------------------------------------------
    |
    | Response decorators are used to decorate both your success- and error
    | responses. A decorator can be disabled by removing it from the list
    | below. You may additionally add your own decorators to the list.
    |
    */

    'adapters' => [
        Paginator::class => [
            Illuminate\Contracts\Pagination\LengthAwarePaginator::class => IlluminatePaginatorAdapter::class,
        ],
        CursorPaginator::class => [
            //
        ],
        Validator::class => [
            \Illuminate\Contracts\Validation\Validator::class => IlluminateValidatorAdapter::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Converted Exceptions
    |--------------------------------------------------------------------------
    |
    | Response decorators are used to decorate both your success- and error
    | responses. A decorator can be disabled by removing it from the list
    | below. You may additionally add your own decorators to the list.
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
        '**' => [
            'code' => 'server_error',
            'status' => 500,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Error
    |--------------------------------------------------------------------------
    |
    | Response decorators are used to decorate both your success- and error
    | responses. A decorator can be disabled by removing it from the list
    | below. You may additionally add your own decorators to the list.
    |
    */

    'fallback_error' => [
        'code' => 'server_error',
        'status' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | Response decorators are used to decorate both your success- and error
    | responses. A decorator can be disabled by removing it from the list
    | below. You may additionally add your own decorators to the list.
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
