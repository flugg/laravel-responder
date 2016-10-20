# Laravel Responder

[![Latest Stable Version](https://poser.pugx.org/flugger/laravel-responder/v/stable?format=flat-square)](https://github.com/flugger/laravel-responder)
[![Packagist Downloads](https://img.shields.io/packagist/dt/flugger/laravel-responder.svg?style=flat-square)](https://packagist.org/packages/flugger/laravel-responder)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](license.md)
[![Build Status](https://img.shields.io/travis/flugger/laravel-responder/master.svg?style=flat-square)](https://travis-ci.org/flugger/laravel-responder)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/flugger/laravel-responder.svg?style=flat-square)](https://scrutinizer-ci.com/g/flugger/laravel-responder/?branch=master)

![Laravel Responder](http://goo.gl/HvmX4j)

Laravel Responder is a package for your JSON API, integrating [Fractal](https://github.com/thephpleague/fractal) into Laravel and Lumen. It can [transform](http://fractal.thephpleague.com/transformers) your Eloquent models and [serialize](http://fractal.thephpleague.com/serializers) your success responses, but it can also help you build error responses, handle exceptions and integration test your API.

## Table of Contents

- [Philosophy](#philosophy)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Accessing the Responder](#accessing-the-responder)
    - [Success Responses](#success-responses)
    - [Transformers](#transformers)
    - [Serializers](#serializers)
    - [Error Responses](#error-responses)
    - [Exceptions](#exceptions)
    - [Testing Helpers](#testing-helpers)
- [Configuration](#configuration)
- [Contributing](#contributing)
- [License](#license)

## Philosophy

When building powerful APIs, you want to make sure your endpoints are consistent and easy to consume by your application. Laravel is a great fit your API, however, it lacks support for common tools like transformers and serializers. Fractal, on the other hand, has some great tools for building APIs and fills in the gaps of Laravel. 

While Fractal solves many of the shortcomings of Laravel, it's often a bit cumbersome to integrate into the framework. Take this example from a controller:

```php
 public function index()
 {
    $manager = new Manager();
    $resource = new Collection(User::get(), new UserTransformer(), 'users');

    return response()->json($manager->createData($resource)->toArray());
 }
```

I admit, the Fractal manager could be moved outside the controller and you could return the array directly. However, as soon as you want a different status code than the default `200`, you probably need to wrap it in a `response()->json()` anyway.

The point is, we all get a little spoiled by Laravel's magic. Wouldn't it be sweet if the above could be written as following:

```php
public function index()
{
    return responder()->success(User::all());
}
```

The package will call on Fractal behind the scenes to automatically transform and serialize the data. No longer will you have to instantiate different Fractal resources depending on if it's a model or a collection, the package deals with all of it automatically under the hood.

## Requirements

This package requires:
- PHP __7.0__+
- Laravel __5.1__+ or Lumen __5.1__+

## Installation

Install the package through Composer:

```shell
composer require flugger/laravel-responder
```

### Laravel

#### Registering the Service Provider

After updating Composer, append the following service provider to the `providers` key in `config/app.php`:

```php
Flugg\Responder\ResponderServiceProvider::class
```

#### Registering the Facade

If you like facades, you may also append the `Responder` facade to the `aliases` key:

```php
'Responder' => Flugg\Responder\Facades\Responder::class
```

#### Publishing Package Assets

You may also publish the package configuration and language file using the Artisan command:

```shell
php artisan vendor:publish
```

This will publish a `responder.php` configuration file in your `config` folder. 

It will also publish an `errors.php` file inside your `lang/en` folder which is used to store your error messages.

### Lumen

#### Registering the Service Provider

Register the package service provider by adding the following line to `app/bootstrap.php`:

```php
$app->register(Flugg\Responder\ResponderServiceProvider::class);
```

#### Registering the Facade

You may also add the following line to `app/bootstrap.php` to register the optional facade:

```php
class_alias(Flugg\Responder\Facades\Responder::class, 'Responder');
```

***
_Remember to uncomment `$app->withFacades();` to enable facades in Lumen._
***

#### Publishing Package Assets

There is no `php artisan vendor:publish` in Lumen, you will therefore have to create your own `config/responder.php` file, if you want to configure the package. Do also note that unlike Laravel there is no `resources/lang` folder, however, you're free to create a `resources/lang/en/errors.php` manually and it will be picked up by the package.

## Usage

The package has a `Flugg\Responder\Responder` service which is responsible for building success and error responses for your API.

### Accessing the Responder

Before you can start making JSON responses, you need to access the responder service. In good Laravel spirit you have multiple ways of doing the same thing:

#### Option 1: Dependency Injection

You may inject the responder service directly into your controller to create success responses:

```php
public function index(Responder $responder)
{
    return $responder->success(User::all());
}
```

You may also create error responses:

```php
return $responder->error('invalid_user');
```

#### Option 2: Facade

Optionally, you may use the `Responder` facade to create responses:

```php
return Responder::success(User::all());
```
```php
return Responder::error('invalid_user');
```

#### Option 3: Helper Method

Additionally, you can use the `responder()` helper method if you're fan of Laravel's `response()` helper method:

```php
return responder()->success(User::all());
```
```php
return responder()->error('invalid_user');
```

Both the helper method and the facade are just different ways of accessing the responder service, so you have access to the same methods.

#### Option 4: Trait

Lastly, the package also has a `Flugg\Responder\Traits\RespondsWithJson` trait you can use in your base controller.

The trait gives you access to `successResponse()` and `errorResponse()` methods in your controllers: 

```php
return $this->successResponse(User::all());
```
```php
return $this->errorResponse('invalid_user');
```

These methods call on the responder service behind the scene.

***
_As described above, you may build your responses in multiple ways. Which way you choose is up to you, the important thing is to stay consistent. We will use the facade for the remaining of the documentation for simplicity's sake._
***

### Success Responses

The responder service has a `success()` method you can use to quickly generate a successful JSON response:

```php
public function index()
{
    return Responder::success(User::all());
}
```

This method returns an instance of `\Illuminate\Http\JsonResponse` and will transform and serialize the data before wrapping it in a JSON response.

#### Setting Transformation Data

The first argument of the `success` method is the transformation data. The transformation data will be transformed if a transformer is set, and must be one of the following types:

##### Eloquent Model

You may pass in a model as the transformation data:

```php
return Responder::success(User::first());
```

##### Collection

You may also pass in a collection of models:

```php
return Responder::success(User::all());
```

##### Array

You may also pass in an array of models:

```php
return Responder::success([User::find(1), User::find(2)]);
```

***
_The array must contain actual model instances, meaning you cannot use `User::all()->toArray()` as transformation data._
***

##### Query Builder

Instead of turning it into a collection, you may pass in a query builder directly:

```php
return Responder::success(User::where('id', 1));
```

The package will then automatically add info pagination data to the response defined by the serializer.

##### Paginator

Additionally, you may limit the amount of items by passing in a paginator:

```php
return Responder::success(User::paginate(5));
```

The package will then automatically add info pagination data to the response, depending on which [serializer](#serializes) you use.

##### Relation

You can also pass in an Eloquent relationship instance:

```php
return Responder::success(User::first()->roles());
```

#### Including Relations

When using Fractal, you include relations using the `parseIncludes()` method on the manager, and add the available relations to the `$availableIncludes` array in your transformer.

With Laravel Responder you don't have to do any of these things. It integrates neatly with Eloquent and automatically parses loaded relations from the model:

```php
return Responder::success(User::with('roles.permissions')->get());
```

#### Setting Status Codes

The status code is set to `200` by default, but can easily be changed by adding a second argument to the `success()` method:

```php
return Responder::success(User::all(), 201);
```

Sometimes you may not want to return anything. In that case you may either pass in null as the first argument or skip it entirely:

```php
return Responder::success(201);
```

#### Adding Meta Data

You may want to pass in additional meta data to the response, you can do so by adding an additional third argument:

```php
return Responder::success(User::all(), 200, ['foo' => 'bar']);
```

You may also omit the status code if you want to send a default `200` response:

```php
return Responder::success(User::all(), ['foo' => 'bar']);
```

### Transformers

A transformer is responsible for transforming your Eloquent models into an array for your API. A transformer may be associated with a model, which means your data will be automatically transformed without having to specify a transformer.

***
_You may read more about how the mapping between a model and transformer work [a few chapters below](#mapping-transformers-to-models)._
***

#### Transforming Data

When using the `success()` method, the package will try to resolve a transformer from the model in the transformation data. If no transformer is found, the model's `toArray()` fields will be returned instead.

If you want to be explicit about which transformer to use, you may call the `transform()` method on the responder service:

```php
return Responder::transform(User::all(), new UserTransformer)->respond();
```

Instead of using a full-blown transformer class, you may also pass in a closure:

```php
return Responder::transform(User::all(), function ($user) {
    return [
        'id' => (int) $user->id,
        'email' => (string) $user->email
    ];
})->respond();
```

If you don't pass in a transformer, it will behave in the same way as the `success()` method:

```php
return Responder::transform(User::all())->respond();
```

Unlike the `success()` method, the `transform()` method returns an instance of `Flugg\Responder\Http\SuccessResponseBuilder`, which is why we chain our call with `respond()` to convert it to an `Illuminate\Http\JsonResponse`.

You can also set the status code or headers using the `respond()` method:

```php
return Responder::transform(User::all())->respond(201, ['x-foo' => 'bar']);
```

You may additionally add any meta data using the `addMeta()` method:

```php
return Responder::transform(User::all())->addMeta(['foo' => 'bar'])->respond();
```

***
_As you might have guessed, the `Responder::success($data, $status, $meta)` method is just a shorthand for calling `Responder::transform($data)->addMeta($meta)->respond($status)`._
***

By using the `serializer()` method you can also explicitly set the serializer:

```php
return Responder::transform(User::all())->serializer(new JsonApiSerializer)->respond();
```

Instead of using `respond()`, you may also convert it to a few other types:

```php
return Responder::transform(User::all())->toArray();
```
```php
return Responder::transform(User::all())->toCollection();
```
```php
return Responder::transform(User::all())->toJson();
```

You can also retrieve the Fractal resource or manager instances:

```php
return Responder::transform(User::all())->getResource();
```
```php
return Responder::transform(User::all())->getManager();
```

#### Creating Transformers

The package gives you an Artisan command you can use to quickly whip up new transformers:

```shell
php artisan make:transformer UserTransformer
```

This will create a new `UserTransformer.php` in the `app/Transformers` folder.

It will automatically resolve what model to inject from the name. For instance, in the example above the package will extract `User` from `UserTransformer` and assume the models live directly in the app folder (as per Laravel's default).

If you store your models somewhere else you may also use the `--model` option to specify model path:

```shell
php artisan make:transformer UserTransformer --model="App\Models\User"
```

You can also use the `--pivot` option to include an additional `transformPivot()` method to transform the model's pivot table:

```shell
php artisan make:transformer UserTransformer --pivot
```

#### Mapping Transformers to Models

In a lot of cases you want to use the same transformer everytime you refer to a model. Instead of passing in a transformer for every response, you can map a transformer to a model, so the model is automatically transformed. 

To map a transformer to a model, your model needs to implement `Flugg\Responder\Contracts\Transformable`. The interface requires a static `transformer()` method, which should return a transformer:

```php
class Role extends Model implements Transformable
{
    /**
     * The transformer used to transform the model data.
     *
     * @return Transformer|callable|string|null
     */
    public static function transformer()
    {
        return RoleTransformer::class;
    }
}
```

The `transformer()` method can also return a closure transformer:

```php
public static function transformer()
{
    return function ($user) {
        return [
            'id' => (int) $user->id,
            'email' => (string) $user->email
        ];
    };
}
```

### Serializers

After your models have been transformed, the data will be serialized using the set serializer. The serializer structures your data output in a certain way, but it can also add additional data like pagination and meta data.

#### Default Serializer

The package brings it own default serializer, `Flugg\Responder\Serializers\ApiSerailizer`. Below is an example response, given a user with a related role:

```json
{
    "status": 200,
    "success": true,
    "data": {
        "id": 1,
        "email": "example@email.com",
        "role": {
            "name": "admin"
        }
    }
}
```

The response output is quite similar to Laravel's default, except it wraps the data inside a `data` field. It also includes a `success` field to quickly tell the user if the request was successful or not.

***
_The `status` field is actually not part of the default serializer, but instead added by the package after serializing the data. You can disable this in the [configuration file](#configuration)._
***

#### Fractal Serializers

If the default serializer is not your cup of tea, you can easily swap it out with one of the three serializers included with Fractal.

##### ArraySerializer

The above example would look like the following using `League\Fractal\Serializers\ArraySerializer`:

```json
{
    "id": 1,
    "email": "example@email.com",
    "role": {
        "name": "admin"
    }
}
```

##### DataArraySerializer

You can also add the `data` field using `League\Fractal\Serializers\DataArraySerializer`:

```json
{
    "data": {
        "id": 1,
        "email": "example@email.com",
        "role": {
            "data": {
                "name": "admin"
            }
        }
    }
}
```

***
_Note how the `data` field applies to every relation as well in this case, unlike the default package serializer._
***

##### JsonApiSerializer

Fractal also has a representation of the [JSON-API](http://jsonapi.org/) standard, using `League\Fractal\Serializers\JsonApiSerializer`:

```json
{
    "data": {
        "type": "users",
        "id": 1,
        "attributes": {
            "email": "example@email.com"
        },
        "relationships": {
            "role": {
                "data": {
                    "type": "roles",
                    "id": 1
                }
            }
        }
    },
    "included": {
        "role": {
            "type": "roles",
            "id": 1,
            "attributes": {
                "name": "admin"
            }
        }
    }
}
```

As you can see, quite more verbose, but it definitely has its uses.

#### Custom Serializers

If none of the above serializers suit your taste, feel free to create your own and set the `serializer` key in the configuration file to point to your serializer class. You can read more about how to create your own serializer in [Fractal's documentation](http://fractal.thephpleague.com/serializers/).

### Error Responses

Just like success responses, you can equally easy generate error responses when something does not go as planned, using the `error()` method:

```php
public function index()
{
    return Responder::error();
}
```

Just like with success responses, this method returns an instance of `\Illuminate\Http\JsonResponse`, the above would return the following JSON:

```json
{
    "status": 500,
    "success": false,
    "error": null
}
```

#### Setting Error Codes

The first parameter of the `error()` method is the error code, which can be any string value:

```php
if (request()->has('bomb')) {
    return Responder::error('bomb_found');
}
```

The above example would include an error object with a set error code:

```json
{
    "status": 500,
    "success": false,
    "error": {
        "code": "bomb_found",
        "message": null
    }
}
```

#### Setting Status Codes

The default status code for error responses is `500`. However, you are free to change the status code by passing in a second argument:

```php
return Responder::error('bomb_found', 400);
```

You may also omit the error code:

```php
return Responder::error(400);
```

#### Setting Error Messages

You might also be interested in providing more descriptive error messages to your responses. You can do so by adding a third parameter to the `error()` method:

```php
return Responder::error('bomb_found', 500, 'No explosives allowed.');
```

Which will output the following JSON:

```json
{
    "status": 500,
    "success": false,
    "error": {
        "code": "bomb_found",
        "message": "No explosives allowed."
    }
}
```

You may also choose to omit the second parameter when responding with the default status code of `500`:

```php
return Responder::error('bomb_found', 'No explosives allowed.');
```

#### Using Language File

You might return the same error response multiple places. Instead of setting the message for each response, you can instead use the `errors.php` language file. This file should be in your `resources/lang/en` folder after you [published your vendor assets](#publishing-package-assets).

***
_If you use Lumen, you need to create the `resources/lang/en/errors.php` file manually. You may simply copy the [default language file](resources/lang/en/errors.php)._
***

The language file contains the following error messages out of the box:

```php
'resource_not_found' => 'The requested resource does not exist.',
'unauthenticated' => 'You are not authenticated for this request.',
'unauthorized' => 'You are not authorized for this request.',
'relation_not_found' => 'The requested relation does not exist.',
'validation_failed' => 'The given data failed to pass validation..',
```

These messages are used for Laravel's default exceptions, which the package can catch and convert to an error JSON response. We'll take a closer look at how to catch these exceptions in the next section on [exceptions](#exceptions).

Let's add the `bomb_found` error code and map it to a corresponding message:

```php
'bomb_found' => 'No explosives allowed.',
```

You can then refer to it from your error response:

```php
return Responder::error('bomb_found');
```

Which will output the same JSON as above, with the error message set:

```json
{
    "status": 500,
    "success": false,
    "error": {
        "code": "bomb_found",
        "message": "No explosives allowed."
    }
}
```

### Exceptions

When something unexpected happens, you might prefer to throw actual exceptions instead of using the `error()` method. And even if you don't, you might want the package to catch Laravel's default exceptions, to automatically convert them to JSON error responses. 

#### Handle Exceptions

If you let the package handle exceptions, the package will catch all exceptions extending `Flugg\Responder\Exceptions\Http\ApiException` and convert them to JSON responses.

To use the package exception handler you need to replace the following line in `app/Exceptions/Handler.php`:

```php
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
```

With the package exception handler:

```php
use Flugg\Responder\Exceptions\Handler as ExceptionHandler;
```

***
_Lumen uses a different base exception handler, and is incompatible with the package exception handler. You may instead, simply copy the contents of the [package exception handler](src/Exceptions/Handler.php) and paste it into your `render()` method and use the `Flugg\Responder\Traits\HandlesApiErrors` trait._
***

#### Catching Laravel Exceptions

Laravel throws a few exceptions when things go wrong. For instance, an `Illuminate\Database\Eloquent\ModelNotFoundException` exception will be thrown when no model is found using the `findOrFail()` method. This exception, and more, are handled by the package if you added the package exception handling, as explained in the paragraph above.

The authorization and validation exceptions thrown from form requests cannot be caught by the package automatically since the exceptions are too generic. However, you may use the `Flugg\Responder\Traits\ThrowsApiErrors` in your base request class:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Flugg\Responder\Traits\ThrowsApiErrors;

abstract class Request extends FormRequest
{
    use ThrowsApiErrors;
}
```

This trait will throw exceptions extending `Flugg\Responder\Exceptions\Http\ApiException` instead, so they are picked up by the package exceptions handler.

***
_After Laravel 5.3 there is no longer a base request class out of the box. You may either create one manually or use the trait in all your form requests._
***

#### Creating Custom Exceptions

The package has a few exceptions out of the box to handle Laravel's default exceptions. However, you may want to create your own exceptions for your API. If you want the package to automatically convert your exceptions to JSON responses, they will need to extend `Flugg\Responder\Exceptions\Http\ApiException`:

```php
<?php

namespace App\Exceptions;

use Flugg\Responder\Exceptions\Http\ApiException;

class CustomException extends ApiException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $statusCode = 400;

    /**
     * The error code used for API responses.
     *
     * @var string
     */
    protected $errorCode = 'custom_error';
}
```

You can customize the response generated from your exceptions by setting the `$statusCode` and `$errorCode` properties as seen above.

### Testing Helpers

Once you start transforming your data, writing tests to test the data becomes increasingly more difficult. You could use methods like Laravel's `seeJson` or `seeJsonEquals`, however, because the data wont be transformed (or serialized) you need to hardcode every value.

The package provides a `Flugg\Responder\Traits\MakesApiRequests` trait you can use in your `tests/TestCase.php` file, to get access to some helper methods to easily test the responses.

***
_Currently, the success response methods only work if you use the default serializer, `Flugg\Responder\Serializers\ApiSerializer`. In the future you can test using all serializers._
***

#### Assert Success Responses

The testing trait provides a `seeSuccess()` method you can use to assert that the success response was successful:

```php
$this->seeSuccess($user, 201);
```

This will transform and serialize your data, just like the `success()` method on the responder. It will run a `seeStatusCode()` on the status code and assert that the response has the right base structure and contains the given data. You may also pass in any meta data as the third parameter.

While the above method only checks if any part of the success data has the values you specified, you can also assert for an exact match:

```php
$this->seeSuccessEquals($user, 201);
```

This works much in the same way as Laravel's `seeJsonEquals`.

#### Assert Error Responses

In the same way as you can assert for success responses, you may also verify that your application sends the right error responses using the `seeError()` method:

```php
$this->seeError('invalid_user', 400);
```

This checks the status code and error response structure. You may also pass in a message as third parameter.

#### Fetch Success Data

You can also easily fetch the data from the response:

```php
$this->json('post', 'sessions', $credentials);
$data = $this->getSuccessData();
```

This will decode the response JSON and return the data as an array.

## Configuration

If you've published vendor assets as explained in the [installation guide](#installation), you will have access to a `config/responder.php` file. You may change the values in this file to change how the package should operate. We'll go through each configuration key.

#### Serializer Class Path

This key represents the full class path to the serializer class you would like the package to use when generating successful JSON responses. You may leave it with the default `Flugg\Responder\Serializers\ApiSerializer`, change it to one of [Fractal's serializers](http://fractal.thephpleague.com/serializers/), or create a [custom one yourself](#custom-serializers).

#### Include Status Code

The package will include a status code for both success- and error responses. You can disable this by setting this key to `false`.

## Contributing

Contributions are more than welcome and you're free to create a pull request on Github. You can run tests with the following command:

```shell
vendor/bin/phpunit
```

If you find bugs or have suggestions for improvements, feel free to submit an issue on Github. However, if it's a security related issue, please send an email to flugged@gmail.com instead.

## License

Laravel Responder is free software distributed under the terms of the MIT license. See [license.md](license.md) for more details.
