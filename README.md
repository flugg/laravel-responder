<p align="center"><img src="https://user-images.githubusercontent.com/1271812/103592890-8c6ef200-4ef4-11eb-935e-f0a52fed4021.png" width="500px"></p>

<p align="center">
    <a href="https://github.com/flugger/laravel-responder/releases"><img src="https://img.shields.io/github/v/tag/flugger/laravel-responder?label=version" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/flugger/laravel-responder"><img src="https://img.shields.io/packagist/dt/flugger/laravel-responder.svg" alt="Packagist Downloads"></a>
    <a href="LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg" alt="Software License"></a>
    <a href='https://github.com/flugg/laravel-responder/actions?query=workflow%3A"Run+tests"'><img src="https://img.shields.io/github/workflow/status/flugg/laravel-responder/Run%20tests/feature/api-resources?label=tests" alt="Tests Status"></a>
    <a href="https://www.codacy.com/gh/flugg/laravel-responder/dashboard"><img src="https://img.shields.io/codacy/coverage/1f14a6f74861492d8d8433019182f5a5/feature/api-resources?branch=feature%2Fapi-resources" alt="Test Coverage"></a>
    <a href="https://github.styleci.io/repos/61958636"><img src="https://github.styleci.io/repos/61958636/shield?style=flat&branch=feature/api-resources" alt="StyleCI"></a>
    <a href="https://www.codacy.com/gh/flugg/laravel-responder/dashboard"><img src="https://img.shields.io/codacy/grade/1f14a6f74861492d8d8433019182f5a5/feature/api-resources?branch=feature%2Fapi-resources" alt="Code Quality"></a>
</p>

Laravel Responder is a package for building API responses in Laravel and Lumen. It supports [API Resources](https://laravel.com/docs/master/eloquent-resources) and gives you the tools to format both success- and error responses consistently.

---

## **2021 Update: Version 4.0 Released!** 🔥

_The package has been rewritten from scratch with a focus on simplifying the code. Now, instead of utilizing [Fractal](https://fractal.thephpleague.com) behind the scenes, the package instead relies on Laravel's own [API Resources](https://laravel.com/docs/master/eloquent-resources). Make sure to check out the [changelog](CHANGELOG.md) and the new documentation to get an overview of all the hot new features._

---

# Table of Contents

-   [Introduction](#introduction)
-   [Requirements](#requirements)
-   [Installation](#installation)
-   [Usage](#usage)
    -   [Creating Responses](#creating-responses)
    -   [Creating Success Responses](#creating-success-responses)
    -   [Creating Error Responses](#creating-error-responses)
    -   [Formatting Responses](#formatting-responses)
    -   [Decorating Responses](#formatting-responses)
    -   [Testing Responses](#testing-responses)
-   [Configuration](#configuration)
-   [Contributing](#contributing)
-   [License](#license)
-   [Donating](#contributing)

# Introduction

Laravel lets you return models directly from a controller method to convert it to JSON. This is a quick way to build APIs but leaves your database columns exposed. There exists multiple solutions out there to add a transformation layer for the data, some of the most popular is [Fractal](https://fractal.thephpleague.com) and [API Resources](https://laravel.com/docs/master/eloquent-resources), where the latter is Laravel's official solution.

This package used to utilize Fractal, but has since moved over to support API resources to make it more streamlined with Laravel. The goal has been to create a high-quality package for building API responses that feels like native Laravel. There has also been put a lot of focus and thought to the documentation. Happy exploration!

# Requirements

This package requires:

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

Add the following line to `app/bootstrap.php` to register the package:

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

This documentation assumes some knowledge of how [API Resources](https://laravel.com/docs/master/eloquent-resources) works.

## Creating Responses

The package has a `Responder` service class, which has a `success` and `error` method to build success- and error responses respectively. To begin creating responses, use the service by picking one of the options below:

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

_Which option you pick is up to you, they are all equivalent, the important thing is to stay consistent. The helper function (option 2) will be used for the remaining of the documentation for brevity._

---

### Using Response Builders

The `success` and `error` methods return a `SuccessResponseBuilder` and `ErrorResponseBuilder` respectively, which both extend an abstract `ResponseBuilder`, giving them common behavior. They will be converted to JSON when returned from a controller, but you can explicitly create an instance of `Illuminate\Http\JsonResponse` with the `respond` method:

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

_Consider always using the `respond` method for consistency's sake._

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

### Formatting Response

To set a formatter to format the responses, you may chain the `formatter` method on one of the response builders:

```php
return responder()->success()->formatter(ExampleFormatter::class)->respond();
```

```php
return responder()->error()->formatter(ExampleFormatter::class)->respond();
```

Read the [Formatting Responses](#formatting-responses) chapter for more information.

### Decorating Response

You can chain the `decorate` method to apply decorators to a response:

```php
return responder()->success()->decorate(ExampleDecorator::class)->respond();
```

```php
return responder()->error()->decorate(ExampleDecorator::class)->respond();
```

Read the [Decorating Responses](#decorating-responses) chapter for more information.

## Creating Success Responses

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

The `success` method takes the response data as the first argument. It accepts most of the same data types as you would normally return from your controllers, however, as it always returns a `JsonResponse` the data must be convertible to an array.

#### Arrays & Collections

You can pass an array or collection as data:

```php
return responder()->success(['id' => 1])->respond();
```

```php
return responder()->success(collect(['id' => 1]))->respond();
```

#### Eloquent Models

You can also pass an Eloquent model or a collection of models:

```php
return responder()->success(User::first())->respond();
```

```php
return responder()->success(User::all())->respond();
```

#### API Resources

```php
return responder()->success(new UserResource(User::first()))->respond();
```

```php
return responder()->success(UserResource::collection(User::all()))->respond();
```

#### Query Builders

Additionally, it supports query builders and relationship instances:

```php
return responder()->success(User::where('name', 'John'))->respond();
```

```php
return responder()->success(User::first()->roles())->respond();
```

---

_The package will run the queries and convert them to collections behind the scenes._

---

#### Paginators

Sending a paginator to the `success` method will attach additional pagination metadata to the response.

```php
return responder()->success(User::paginate())->respond();
```

Assuming there are no results and the default formatter is used, the JSON output would look like:

```json
{
    "data": [],
    "pagination": {
        "total": 0,
        "count": 0,
        "perPage": 15,
        "currentPage": 1,
        "totalPages": 1,
        "links": [
            ...
        ]
    }
}
```

---

_You can modify the response using a different [response formatter](#formatting-responses)._

---

### Adding metadata

You may want to attach additional metadata to the response. You can do this using the `meta` method:

```php
return responder()->success(User::all())->meta(['count' => User::count()])->respond();
```

Using the default formatter, the metadata will simply be appended to the response array:

```json
{
    "data": [],
    "count": 0
}
```

## Creating Error Responses

Whenever a consumer of your API does something unexpected, you can return an error response describing the problem. As briefly shown in a previous chapter, an error response can be created using the `error` method:

```php
return responder()->error()->respond();
```

The error response has knowledge about an error code, a corresponding error message and optionally some error data. With the default configuration, the above code would output the following JSON:

```json
{
    "success": false,
    "status": 500,
    "error": {
        "code": null,
        "message": null
    }
}
```

### Setting Error Code & Message

You can fill the first parameter of the `error` method to set an error code:

```php
return responder()->error('sold_out_error')->respond();
```

---

_You may optionally use integers for error codes._

---

In addition, you may set the second parameter to an error message describing the error:

```php
return responder()->error('sold_out_error', 'The requested product is sold out.')->respond();
```

#### Set Messages In Language Files

You can set the error messages in a language file, which allows for returning messages in different languages. The configuration file has an `error_message_files` key defining a list of language files with error messages. By default, it is set to `['errors']`, meaning it will look for an `errors.php` file inside `resources/lang/en`. You can use these files to map error codes to corresponding error messages:

```php
return [
    'sold_out_error' => 'The requested product is sold out.',
];
```

#### Register Messages Using `ErrorMessageRegistry`

Instead of using language files, you may alternatively set error messages directly on the `ErrorMessageRegistry` class. You can place the code below within `AppServiceProvider` or an entirely new `TransformerServiceProvider`:

```php
use Flugg\Responder\ErrorMessageRegistry;

public function boot()
{
    $this->app->make(ErrorMessageRegistry::class)->register([
        'sold_out_error' => 'The requested product is sold out.',
    ]);
}
```

## Formatting Responses

## Decorating Responses

## Testing Responses

# Configuration

# Contributing

Contributions are more than welcome and you're free to create a pull request on Github. See [contributing.md](contributing.md) for more details.

# License

Laravel Responder is free software distributed under the terms of the MIT license. See [license.md](license.md) for more details.

# Donating
