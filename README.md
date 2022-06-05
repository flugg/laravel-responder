<p align="center"><img src="https://user-images.githubusercontent.com/1271812/111231999-824f2b00-85ea-11eb-9afc-c0176529a234.png" width="500px"></p>

<p align="center">
    <a href="https://github.com/flugger/laravel-responder/releases"><img src="https://img.shields.io/github/v/tag/flugger/laravel-responder?label=version" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/flugger/laravel-responder"><img src="https://img.shields.io/packagist/dt/flugger/laravel-responder.svg" alt="Packagist Downloads"></a>
    <a href="LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg" alt="Software License"></a>
    <a href='https://github.com/flugg/laravel-responder/actions?query=workflow%3A"Run+tests"'><img src="https://img.shields.io/github/workflow/status/flugg/laravel-responder/Run%20tests/feature/api-resources?label=tests" alt="Tests Status"></a>
    <a href="https://www.codacy.com/gh/flugg/laravel-responder/dashboard"><img src="https://img.shields.io/codacy/coverage/1f14a6f74861492d8d8433019182f5a5/feature/api-resources?branch=feature%2Fapi-resources" alt="Test Coverage"></a>
    <a href="https://github.styleci.io/repos/61958636"><img src="https://github.styleci.io/repos/61958636/shield?style=flat&branch=feature/api-resources" alt="StyleCI"></a>
    <a href="https://www.codacy.com/gh/flugg/laravel-responder/dashboard"><img src="https://img.shields.io/codacy/grade/1f14a6f74861492d8d8433019182f5a5/feature/api-resources?branch=feature%2Fapi-resources" alt="Code Quality"></a>
</p>

Laravel Responder is a package for building API responses in Laravel and Lumen. It supports [API Resources](https://laravel.com/docs/master/eloquent-resources) and formats your success- and error responses consistently.

---

## **2022 Update: Version 4.0 Released!** 🔥

_The package has been rewritten from scratch with a focus on simplifying the code. Now, instead of utilizing [Fractal](https://fractal.thephpleague.com) behind the scenes, the package instead relies on Laravel's own [API Resources](https://laravel.com/docs/master/eloquent-resources). Make sure to check out the [changelog](CHANGELOG.md) and the new documentation to get an overview of all the hot new features._

---

# Table of Contents

-   [Introduction](#introduction)
-   [Requirements](#requirements)
-   [Installation](#installation)
-   [Usage](#usage)
    -   [Success Responses](#success-responses)
    -   [Normalizers](#normalizers)
    -   [Pagination](#pagination)
    -   [Error Responses](#error-responses)
    -   [Formatters](#formatters)
    -   [Decorators](#formatters)
    -   [Testing](#testing-responses)
-   [License](#license)
-   [Contributing](#contributing)
-   [Donating](#contributing)

# Introduction

When building JSON APIs, it's crucial to be consistent with the structure of the responses. Often, people will directly return Eloquent models from their controllers. Not only will this leave your database columns exposed, but it's tricky to transform the data or attach metadata.

Laravel released [API resources](https://laravel.com/docs/master/eloquent-resources) to solve this. It provides a transformation layer between the data and the response. However, it does have some limitations when it comes to enforcing a response structure throughout your project.

This package solves this by introducing an additional formatting layer. Furthermore, it will provide you the tools you need to build success- and error responses consistently. The goal has been to create a high-quality package for building API responses that feels like native Laravel.

# Requirements

The package requires:

-   PHP **7.3**+
-   Laravel **8.0**+ or Lumen **8.0**+

# Installation

To get started, install the package through Composer:

```shell
composer require flugger/laravel-responder
```

## Laravel

The package supports auto-discovery, so the `ResponderServiceProvider` provider and `Responder` facade will automatically be registered by Laravel.

#### Publish Configuration _(optional)_

You may additionally publish the package configuration using the `vendor:publish` Artisan command:

```shell
php artisan vendor:publish --provider="Flugg\Responder\ResponderServiceProvider"
```

This will publish a `responder.php` configuration file in your `config` folder.

## Lumen

#### Register Service Provider

Add the following line to `app/bootstrap.php` to register the service provider:

```php
$app->register(Flugg\Responder\ResponderServiceProvider::class);
```

#### Register Facade _(optional)_

You may also add the following lines to `app/bootstrap.php` to register the `Responder` facade:

```php
class_alias(Flugg\Responder\Facades\Responder::class, 'Responder');
```

---

There is no `vendor:publish` command in Lumen, so you'll have to create your own `config/responder.php` file if you want to configure the package.

---

# Usage

The package has a `Responder` service class, which has a `success` and `error` method to build success- and error responses, respectively. To begin creating responses, use the service by picking one of the options below:

#### Option 1: Inject `Responder` Service

You may inject the `Flugg\Responder\Responder` service class directly into your controller methods:

```php
public function index(Responder $responder)
{
    return $responder->success();
}
```

You can also use the `error` method to create error responses:

```php
return $responder->error();
```

#### Option 2: Use `responder` Helper

If you're a fan of Laravel's `response` helper function, you may like the `responder` helper function:

```php
return responder()->success();
```

```php
return responder()->error();
```

#### Option 3: Use `Responder` Facade

Optionally, you may use the `Responder` facade to create responses:

```php
return Responder::success();
```

```php
return Responder::error();
```

#### Option 4: Use `MakesJsonResponses` Trait

Lastly, the package provides a `Flugg\Responder\MakesJsonResponses` trait you can use in your controllers:

```php
return $this->success();
```

```php
return $this->error();
```

---

_Which option you pick is up to you; they are all equivalent. The important thing is to stay consistent. We'll use the helper function in the examples below for the sake of brevity._

---

### Using Response Builders

The `success` and `error` methods return a `SuccessResponseBuilder` and `ErrorResponseBuilder` respectively, which both extend an abstract `ResponseBuilder` giving them shared behavior. They will be converted to JSON when returned from a controller, but you can explicitly create an instance of `Illuminate\Http\JsonResponse` with the `respond` method:

```php
return responder()->success()->respond();
```

```php
return responder()->error()->respond();
```

The status code is set to `200` and `500` by default, but can be changed by setting the first parameter. You can also pass a list of headers as the second argument:

```php
return responder()->success()->respond(201, ['x-foo' => 123]);
```

```php
return responder()->error()->respond(404, ['x-foo' => 123]);
```

---

_Consider always using the `respond` method to be consistent._

---

### Casting Response Data

Instead of converting the response to a `JsonResponse` using the `respond` method, you can cast the response data to a few other types, like an array:

```php
return responder()->success()->toArray();
```

```php
return responder()->error()->toArray();
```

You also have a `toCollection` and `toJson` method at your disposal.

### Attaching Metadata

You may want to attach additional metadata to the response output. You can do this using the `meta` method:

```php
return responder()->success()->meta(['verson' => 1])->respond();
```

```php
return responder()->error()->meta(['version' => 1])->respond();
```

### Formatting Response

To set a formatter to format the responses, you may chain the `formatter` method on one of the response builders:

```php
return responder()->success()->formatter(ExampleFormatter::class)->respond();
```

```php
return responder()->error()->formatter(ExampleFormatter::class)->respond();
```

Read the [Formatters](#formatters) chapter for more information.

### Decorating Response

You can chain the `decorate` method to apply decorators to a response:

```php
return responder()->success()->decorate(ExampleDecorator::class)->respond();
```

```php
return responder()->error()->decorate(ExampleDecorator::class)->respond();
```

Read the [Decorators](#decorators) chapter for more information.

## Success Responses

As briefly demonstrated above, success responses are created using the `success` method:

```php
return responder()->success()->respond();
```

Assuming we use the default formatter, the above code would output the following JSON:

```json
{
    "data": null
}
```

### Setting Response Data

The `success` method accepts the response data as the first parameter, which can be an array:

```php
return responder()->success(['success' => true])->respond();
```

It can also be primitives like `string`, `boolean`, `integer`, `float` and `null`:

```php
return responder()->success('success')->respond();
```

```php
return responder()->success(true)->respond();
```

```php
return responder()->success(3.14)->respond();
```

```php
return responder()->success(null)->respond();
```

In addition, the `success` method accepts Eloquent models, collections, API resources and more, using normalizers. More on that in the [Normalizers](#normalizers) chapter.

### Setting Resource Key

A resource key can be used to give a namespace to the provided data. This key can then be used by a formatter, like `JsonApiFormatter`, to add additional information to the response data. A resource key can be set by providing a second parameter to the `success` method:

```php
return responder()->success(null, 'users')->respond();
```

The normalizers will extract a resource key automatically when given Eloquent models or API resources, by using the model's table name. To override the resource key you may add a `getResourceKey` method to your model or API resource class:

```php
public function getResourceKey(): string
{
    return 'users';
}
```

### Attaching Paginators

The success response builder also has a `paginator` and `cursor` method to attach a paginator or cursor paginator respectively. We'll look more closely at this in the [Pagination](#pagination) chapter.

## Normalizers

A normalizer is a class that tells the package how to normalize an object. The package comes packed with a list of default normalizers in the configuration file, which allows for the following data types:

#### Eloquent Models

```php
return responder()->success(User::first())->respond();
```

#### Collections

```php
return responder()->success(User::all())->respond();
```

```php
return responder()->success(collect(['success' => true]))->respond();
```

#### Query Builders

```php
return responder()->success(User::where('active', true))->respond();
```

```php
return responder()->success(DB::table('users')->where('active', true))->respond();
```

```php
return responder()->success(User::roles())->respond();
```

---

_The normalizers will run the queries for the examples above. For relations, it will only normalize it to a collection of arrays if it's a one-to-many or many-to-many relationship._

---

#### Paginators

```php
return responder()->success(User::paginate())->respond();
```

#### API Resources

```php
return responder()->success(new UserResource(User::first()))->respond();
```

```php
return responder()->success(UserResource::collection(User::all()))->respond();
```

---

_Since the package will format the data using a formatter, you should think of API resources as pure transformers for your models. This means the `withResponse` method in API resources is ignored and the package doesn't utilize [data wrapping](https://laravel.com/docs/8.x/eloquent-resources#data-wrapping)._

---

#### Arrayable

Lastly, there's a normalizer which calls `toArray` on any object which implements `Illuminate\Contracts\Support\Arrayable`.

### Creating Normalizers

If you want to extend the package's normalizing capabilities, you may create your own normalizers. For instance, we could create a `StdClassNormalizer` to normalize `stdClass` to an array:

```php
namespace App\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use stdClass;

class StdClassNormalizer implements Normalizer
{
    protected $data;

    public function __construct(stdClass $data)
    {
        $this->data = $data;
    }

    public function normalize(): SuccessResponse
    {
        $resource = new Item((array) $this->data);

        return new SuccessResponse($resource);
    }
}
```

---

_The constructor allows for injecting other dependencies besides accepting the `$data` parameter to be normalized._

---

You might have noticed the `normalize` method of the `StdClassNormalizer` above is returning a `SuccessResponse`. This is a data transfer object you can use to build a response from the object. The constructor requires a resource object to define the type of data attached to the response.

#### Creating Resource Objects

An `Item` defines a single resource array:

```php
new SuccessResponse(new Item(['id' => 1]));
```

`Item` accepts a resource key and a map of relationships as the second and third arguments:

```php
new SuccessResponse(new Item(['id' => 1], 'users', [
    'profile' => new Item(['id' => 2])
]));
```

You can also use the `Collection` class to define a collection of resource arrays. It accepts a list of item objects as the first argument, and a resource key as the second:

```php
new SuccessResponse(new Collection([
    new Item(['id' => 1]),
    new Item(['id' => 2]),
], 'users'));
```

Lastly, you may use the `Primitive` class to define a scalar value like `string`, `boolean`, `integer`, `float` and `null`. It also accepts a resource key as the second parameter:

```php
new SuccessResponse(new Primitive(3.14, 'users'));
```

#### Building a Success Response Object

You can use the `setStatus` method to set a status code:

```php
new SuccessResponse(new Item(['id' => 1]))->setStatus(201);
```

The `setHeaders` method can be used to attach headers to the response:

```php
new SuccessResponse(new Item(['id' => 1]))->setHeaders(['x-foo' => 123]);
```

Additionally, the `setMeta` method will set included metadata:

```php
new SuccessResponse(new Item(['id' => 1]))->setMeta(['version' => 1]);
```

---

_You also have a `setPaginator` and `setCursor` which accepts the same values as the `paginator` and `cursor` methods on the success response builder. More about that in the [Pagination](#pagination) chapter._

---

### Applying Normalizers

To apply the `StdClassNormalizer` normalizer we made above, add a mapping to the configuration file:

```php
'normalizers' => [
    // ...
    \stdClass::class => StdClassNormalizer::class,
],
```

Now, any `stdClass` object sent in will automatically be normalized.

---

_The order of configured normalizers do matter; the first normalizer that is an instance of the data provided will be used._

---

## Pagination

We've already looked at how the success response builder accepts a Laravel paginator instance using normalizers. If you want to use a different pagination library, you can either create your own normalizer, or manually attach a paginator to the response. For instance, instead of relying on the normalizer, we could have attached a Laravel paginator manually:

```php
$paginator = User::paginate();

return responder()
    ->success($paginator->getCollection())
    ->paginator(new IlluminatePaginatorAdapter($paginator))
    ->respond();
```

The `paginator` expects a `Flugg\Responder\Contracts\Pagination\Paginator` instance. The package provides you with an `IlluminatePaginatorAdapter` to make Laravel's paginator compatible with the package.

### Cursor Paginators

Since Laravel has no concept of cursor pagination, this package doesn't come with a cursor pagination normalizer out of the box. However, the success response builder does come with a `cursor` method which accepts a `Flugg\Responder\Contracts\Pagination\CursorPaginator` instance. You can create your own adapter using the cursor pagination library of your choice.

## Error Responses

Whenever a consumer of your API does something unexpected, you should return an error response describing the problem. As briefly shown in the first chapter, error responses are created using the `error` method:

```php
return responder()->error()->respond();
```

Using the default formatter, the above code would output the following JSON:

```json
{
    "error": {
        "code": null
    }
}
```

### Setting Error Code & Message

You can set the first parameter of the `error` method to set an error code:

```php
return responder()->error('error_occured')->respond();
```

---

_You may optionally use integers for error codes._

---

In addition, you may send in a more detailed error message as the second argument:

```php
return responder()->error('error_occured', 'An error has occured')->respond();
```

#### Configuring Error Messages

You might want to use the same error codes in multiple places, and probably don't want to repeat error messages. If you don't send in a second argument to the `error` method, the package will look in the configuration file for an error message. You may append your own error messages to the `error_messages` key:

```php
'error_messages' => [
    // ...
    'error_occured' => 'An error has occured'
]
```

#### Manually Register Error Messages

Instead of adding the error messages to the configuration file, you may register them using the `ErrorMessageRegistry` class. For instance, you may use this to register localized error messages from a language file:

```php
use Flugg\Responder\ErrorMessageRegistry;

public function boot()
{
    $this->app->make(ErrorMessageRegistry::class)
        ->register(\Lang::get('error_messages'));
}
```

---

_This assumes you have a `error_messages.php` file in your `resources/lang` folder. You may put this in `AppServiceProvider` or another service provider._

---

### Using Exceptions

Often you want to return an error response when you catch an exception. The `error` method accepts an instance of an exception as the second parameter, and will resolve the error message from the message stored in the exception:

```php
try {
    throw new Exception('An error has occured');
} catch (Exception $exception) {
    return responder()->error('error_occured', $exception)->respond();
}
```

Additionally, you may even skip the first parameter and just pass in an exception. The package will then resolve the error code based on the name of the exception class. For instance, an error code of `error_occured` will be resolved from the example below:

```php
try {
    throw new ErrorOccuredException('An error has occured');
} catch (ErrorOccuredException $exception) {
    return responder()->error($exception)->respond();
}
```

#### Configuring Exceptions

Instead of resolving the error code from the name, you may add an exception to error response mapping in the configuration file to automatically resolve error code and status code. For instance, you can add the following mapping to the `exceptions` key:

```php
'exceptions' => [
    // ...
    \App\Exceptions\CustomException::class => [
        'code' => 'error_occured',
        'status' => 400,
    ],
]
```

The error message will then be resolved from the `error_messages` key in the configuration if a key of `error_occured` exists. Else, it will resolve the error message from the exception itself. You may also pass a second argument to the `error` method to override the error message on a per-response basis.

### Handling Exceptions

After you've installed the package, it will automatically catch and convert all of Laravel's standard exceptions to error responses. Below is a list of all the exceptions that are converted to an error response, from the configuration file:

| Exception                                                              | Error Code           | Status |
| ---------------------------------------------------------------------- | -------------------- | ------ |
| `Illuminate\Auth\AuthenticationException`                              | `unauthenticated`    | `401`  |
| `Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException`     | `unauthorized`       | `403`  |
| `Symfony\Component\HttpKernel\Exception\NotFoundHttpException`         | `page_not_found`     | `404`  |
| `Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException` | `method_not_allowed` | `405`  |
| `Illuminate\Database\Eloquent\RelationNotFoundException`               | `relation_not_found` | `422`  |
| `Illuminate\Validation\ValidationException`                            | `validation_failed`  | `422`  |
| `Exception`                                                            | `server_error`       | `500`  |

The package will only convert the exceptions during a JSON request. In additional, it will only convert errors with a status code 500 or above in production, so you can debug exceptions during development.

You can add your own exceptions to the `exceptions` list in the configuration file to automatically convert them to error responses. This will allow your controllers to implicitly return error responses when an exception is thrown.

### Attaching Validators

In the previous section, we saw that the package formats validation errors when a `ValidationException` is thrown. However, the error response builder also has a `validator` method to manually attach a validator to the response, which accepts a `Flugg\Responder\Contracts\Validation\Validator` instance. The package ships with an `IlluminateValidatorAdapater` for Laravel validators:

```php
$validator = Validator::make(request()->all(), [
    'body' => 'required',
]);

if ($validator->fails()) {
    return responder()
        ->error('validation_failed')
        ->validator(new IlluminateValidatorAdapater($validator))
        ->respond();
}
```

Feel free to create your own adapter and attach it to the `validator` method to support other validation libraries.

## Formatters

## Decorators

Decorators allow for last minute changes to the response before it's returned. The package ships with two decorators out of the box which are commented out under the `decorators` key in the configuration. Feel free to uncomment the decorators you want to enable.

#### PrettyPrintDecorator

This decorator will beautify the response data using PHP's `JSON_PRETTY_PRINT` when encoding to JSON.

#### EscapeHtmlDecorator

This decorator is based on the "sanitize input, escape output" concept and will escape HTML entities in all strings in the response data. You can securely store input data "as is" (even malicious HTML tags) being sure that it will be outputted as un-harmful strings.

### Creating Decorators

You can create your own decorator by extending the `ResponseDecorator` class, and overriding the `make` method. For instance, if we want to add a `x-foo` header to all responses:

```php
namespace App\Http\Decorators;

use Flugg\Responder\Http\Decorators\ResponseDecorator;
use Illuminate\Http\JsonResponse;

class ExampleDecorator extends ResponseDecorator
{
    public function make(array $data, int $status, array $headers = []): JsonResponse
    {
        return parent::make($data, $status, array_merge($headers, ['x-foo' => 123]));
    }
}
```

### Applying Decorators

There's two ways to apply a decorator, either globally through the configuration, or on a per-response basis.

#### Decorate All Responses

Apply the decorator to the `decorators` key of the configuration file:

```php
'decorators' => [
    // ...
    ExampleDecorator::class
]
```

#### Decorate a Specific Response

You may decorate a single response by using the `decorate` method of a response builder:

```php
return responder()->success()->decorate(ExampleDecorator::class)->respond();
```

```php
return responder()->error()->decorate(ExampleDecorator::class)->respond();
```

## Testing

# License

Laravel Responder is free software distributed under the terms of the MIT license. See [LICENSE.md](LICENSE.md) for more details.

# Contributing

Contributions are more than welcome and you're free to create a pull request on Github. See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for more details.

# Donating

Several hundred hours have been dedicated for this package. If you like what you see, please do consider showing your support by sponsoring me. This will also give me more time to work on open source in the future. See [Sponsor Me](license.md) for more details. I highly appreciate your support! ❤️
