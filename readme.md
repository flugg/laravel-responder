# Laravel Responder

[![Latest Stable Version](https://poser.pugx.org/flugger/laravel-responder/v/stable?format=flat-square)](https://github.com/flugger/laravel-responder)
[![Packagist Downloads](https://img.shields.io/packagist/dt/flugger/laravel-responder.svg?style=flat-square)](https://packagist.org/packages/flugger/laravel-responder)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](license.md)
[![Build Status](https://img.shields.io/travis/flugger/laravel-responder/master.svg?style=flat-square)](https://travis-ci.org/flugger/laravel-responder)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/flugger/laravel-responder.svg?style=flat-square)](https://scrutinizer-ci.com/g/flugger/laravel-responder/?branch=master)

![Laravel Responder](http://goo.gl/HvmX4j)

Laravel Responder is a package for your JSON APIs, integrating [Fractal](http://fractal.thephpleague.com) into Laravel and Lumen. It can [transform](http://fractal.thephpleague.com/transformers) your Eloquent models and [serialize](http://fractal.thephpleague.com/serializers) your success responses, but it can also help you build error responses, handle exceptions and integration test your API.

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

When building powerful APIs, you want to make sure your endpoints are consistent and easy to consume by your application. Laravel is a great fit for your API, however, it lacks support for common tools like transformers and serializers. Fractal, on the other hand, has some great tools for building APIs and fills in the gaps of Laravel. 

While Fractal solves many of the shortcomings of Laravel, it's often a bit cumbersome to integrate into the framework. Take this example from a controller:

```php
 public function index()
 {
    $users = User::all();
    $manager = new Manager();
    $resource = new Collection($users, new UserTransformer(), 'users');

    return response()->json($manager->createData($resource)->toArray());
 }
```

I admit, the Fractal manager could be moved outside the controller and you could return the array directly. However, as soon as you want a different status code than the default `200`, you probably need to use `response()->json()` anyway.

The point is, we all get a little spoiled by Laravel's magic. Wouldn't it be sweet if the above could be written as following:

```php
public function index()
{
    $users = User::all();

    return responder()->success($users);
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

You may also register the facade by adding the following line to `app/bootstrap.php`:

```php
class_alias(Flugg\Responder\Facades\Responder::class, 'Responder');
````

***
_Remember to uncomment `$app->withFacades();` to enable facades in Lumen._
***

#### Publishing Package Assets

There is no `php artisan vendor:publish` in Lumen, you will therefore have to create your own `config/responder.php` file, if you want to configure the package. Do also note that unlike Laravel there is no `resources/lang` folder, however, you're free to create a `resources/lang/en/errors.php` manually and it will be picked up by the package.

## Usage

The package has a `Flugg\Responder\Responder` service which is responsible for building success- and error responses for your JSON API. The class has a `success()` and `error()` method which both returns an instance of `Illuminate\Http\JsonResponse`.

### Accessing the Responder

Before you can start making JSON responses, you need to access the responder service. In good Laravel spirit you have multiple ways of doing the same thing:

#### Option 1: Dependency Injection

You may inject the responder service directly into your controller to create success responses:

```php
public function index(Responder $responder)
{
    $users = User::all();
    
    return $responder->success($users);
}
```

You may also create error responses:

```php
return $responder->error('invalid_user');
```

#### Option 2: Facade

Optionally, you may use the `Responder` facade to create responses:

```php
return Responder::success($users);
```
```php
return Responder::error('invalid_user');
```

#### Option 3: Helper Method

Additionally, you can use the `responder()` helper method if you're fan of Laravel's `response()` helper method:

```php
return responder()->success($users);
```
```php
return responder()->error('invalid_user');
```

Both the helper method and the facade are just different ways of accessing the responder service, so you have access to the same methods.

#### Option 4: Trait

Lastly, the package also has a `Flugg\Responder\Traits\RespondsWithJson` trait you can use in your base controller.

The trait gives you access to `successResponse()` and `errorResponse()` methods in your controllers: 

```php
return $this->successResponse($users);
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

This method returns an instance of `\Illuminate\Http\JsonResponse` and will both transform and serialize the data. The first argument is the transformation data.

#### Setting Transformation Data

The transformation data will be transformed if a transformer is set, and must be one of the following types:

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

The package will then automatically add info pagination data to the response, depending on which [serializer](#serializes) you use.

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
return Responder::success(User::with('roles.permissions')->all());
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
return Responder::success($user, ['foo' => 'bar']);
```

### Transformers

Transformers are classes which only have one responsibility; to transform one set of data to another. In our case we want to transform an Eloquent model into an array. The package provides its own abstract transformer, `Flugg\Responder\Transformer`. This transformer extends `League\Fractal\Transformer` and adds integration with Eloquent.

Your transformers should extend the package transformer as follows:

```php
<?php

namespace App\Transformer;

use App\User;
use Flugg\Responder\Transformer;

class UserTransformer extends Transformer
{
    /**
     * Transform the model data into a generic array.
     *
     * @param  User $user
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id'       => (int) $user->id,
            'email'    => $user->email,
            'fullName' => $user->first_name . ' ' . $user->last_name
        ];
    }
}
```

Transformers basically give you a way to abstract your database logic from your API design, and transforms all values to the correct type. As seen in the example above, we cast the user id to an integer. Then we concatenate the first- and last name together, and only expose a `fullName` field to the API.

***
_Note how we're converting snake case fields to camel case. You can read more about it in the [Converting to Camel Case](#converting-to-camel-case) section._
***

#### Transforming Pivots

When you include a many to many relationship to your response, you may want to transform the pivot table data, to expose the data to the API. You may do so by adding an additional transform method, `transformPivot()` to your transformer:

```php
<?php

namespace App\Transformer;

use App\User;
use Flugg\Responder\Transformer;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserTransformer extends Transformer
{
    /**
     * Transform the model data into a generic array.
     *
     * @param  User $user
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id' => (int) $user->id
        ];
    }

    /**
     * Transform the model's pivot table data into a generic array.
     *
     * @param  Pivot $pivot
     * @return array
     */
    public function transformPivot(Pivot $pivot):array
    {
        return [
            'user_id' => $pivot->user_id,
            'role_id' => $pivot->role_id
        ];
    }
}
```

In the example above, we assume we have a many to many relationship between a `User` and `Role`. Note how the new method takes in an instance of `Illuminate\Database\Eloquent\Relations\Pivot`, which is what a many to many relation returns. If it's a polymorphic many to many relationship, you may instead inject an instance of `Illuminate\Database\Eloquent\Relations\MorphPivot`.

Continuing on the example and using the response below, it will automatically look for a `transformPivot()` method in your `UserTransformer`:

````php
$role = Role::with('users')->find(1);

return Responder::success($role);
```

However, if you do the inverse, you will have to place the `transformPivot()` method in your `RoleTransformer`:

````php
$user = User::with('roles')->find(1);

return Responder::success($user);
```

The reasoning behind this is because the `pivot` field is appended to the related resource.

#### Creating Transformers

The package gives you an Artisan command you can use to quickly generate new transformers:

```shell
php artisan make:transformer UserTransformer
```

This will create a new `UserTransformer.php` in an `app/Transformers` folder.

It will automatically resolve what model to inject from the name. For instance, in the example above the package will extract `User` from `UserTransformer` and assume the models live directly in the app folder (as per Laravel's default).

If you store your models somewhere else you may also use the `--model` option to specify model path:

```shell
php artisan make:transformer UserTransformer --model="App\Models\User"
```

You can also use the `--pivot` option to also include an additional method to transform the model's pivot table:

```shell
php artisan make:transformer UserTransformer --pivot
```

#### Mapping Transformers to Models

When you pass in a model or a collection of models into the `success` method, the package will automatically transform the models. However, because you're free to place your transformers anywhere you want, the package has no way of knowing which transformer to use for each model. 

To map a transformer to a model, you need to implement `Flugg\Responder\Contract\Transformable` in your models. The interface requires you to create a static `transformer()` method, which should return the path to the corresponding transformer:

```php
<?php

namespace App;

use App\Transformers\FruitTransformer;
use Flugg\Responder\Contracts\Transformable;
use Illuminate\Database\Eloquent\Model;

class Fruit extends Model implements Transformable
{
    /**
     * The path to the transformer class.
     *
     * @return string|null
     */
    public static function transformer()
    {
        return FruitTransformer::class;
    }
}
```

All models you expose to the API should implement the `Flugg\Responder\Contract\Transformable` contract. If you don't want to transform a given model, you can simply return `null`:

```php
public static function transformer()
{
    return null;
}
```

#### Converting to Camel Case

You may want to expose all fields in your API in camel case, however, Eloquent uses snake case attributes by default. A transformer is one of the last things that take place before the data is returned to the API, and is a perfect location to do the conversion to camel casing:

```php
public function transform(User $user)
{
    return [
        'id'        => (int) $user->id,
        'roleId'    => (int) $user->permission_id,
        'isAdmin'   => (bool) $user->is_admin,
        'createdAt' => (string) $user->created_at,
        'updatedAt' => (string) $user->updated_at
    ];
}
```

This is great, but only works for API responses, and not for request parameters. Imagine you create a user from the request input with camel case fields:

```php
User::create(request()->all());
```

That wont work because the user model expects snake case fields. However, the package has a `Flugg\Responder\Traits\ConvertsParameters` trait, which you can use in your `app/Http/Requests/Request.php` file to automatically convert all incoming parameters to snake case before reaching the controller:

```php
<?php

namespace App\Http\Requests;

use Flugg\Responder\Traits\ConvertsParameters;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    use ConvertsParameters;
}
```

This trait will not only convert all incoming parameters to snake case, it will also convert all `'true'` and `'false'` values to actual booleans. If you only want one of the conversions, you may set the following variables on the request:

```php
<?php

namespace App\Http\Requests;

use Flugg\Responder\Traits\ConvertsParameters;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    use ConvertsParameters;
    
    /**
     * Automatically cast all string booleans to booleans.
     *
     * @var bool
     */
    protected $castBooleans = false;
    
    
    /**
     * Automatically convert all parameter keys to snake case.
     *
     * @var bool
     */
    protected $convertToSnakeCase = false;
}
```

You may also convert any parameters manually using the `convertParameters()` method in your request:

```php
/**
 * Convert incoming parameters.
 *
 * @param  array $parameters
 * @return array
 */
protected function convertParameters(array $parameters)
{
    $parameters['included'] = $parameters['included'] ?? [];
    
    return $parameters;
}
```

The method takes in an array of all incoming parameters, which you may modify to your liking before returning it again. In the example above, we set the `included` parameter to an empty array if it's not set using the new [null coalesce operator in PHP7](https://wiki.php.net/rfc/isset_ternary). This way we can use it as an argument in Eloquent's `with()` and `load()` methods, as these methods require the first parameter to be an array.

### Serializers

After your models have been transformed, the data will be serialized using the serializer set in the `responder.php` configuration file. The serializer structures your data output in a certain way. It can also add additional data like pagination information and meta data.

When all responses are serialized with the same serializer, you end up with a consistent API, and if you want to change the structure in the future, you can simply swap out the serializer.

#### Default Serializer

The package brings its own serializer `Flugg\Responder\Serializers\ApiSerailizer`, which is the default serializer. Below is an example response with a user model, with a related role model:

```json
{
    "status": 200,
    "success": true,
    "data": {
        "id": 1,
        "email": "example@email.com",
        "fullName": "John Doe",
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
    "fullName": "John Doe",
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
        "fullName": "John Doe",
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
            "email": "example@email.com",
            "fullName": "John Doe"
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

Just like we've been generating success responses, you can equally easy generate error responses when something does not go as planned:

```php
public function index()
{
    if (request()->has('bomb')) {
        return Responder::error('bomb_found');
    }
}
```

The only required argument to the `error()` method is an error code. You can use any string you like for the error code, and later on we will map these to corresponding error messages.

The example above will return the following JSON response:

```json
{
    "status": 500,
    "success": false,
    "error": {
        "code": "bomb_found"
    }
}
```

The default status code for error responses is `500`. However, you can change the status code by passing in a second argument:

```php
return Responder::error('bomb_found', 400);
```

#### Setting Error Messages

An error code is useful for many reasons, but it might not give enough clues to the user about what caused the error. So you might want to add a more descriptive error message to the response. You can do so by passing in a third argument to the `error()` method:

```php
return Responder::error('bomb_found', 400, 'No explosives allowed in this request.');
```

Which will output the following JSON:

```json
{
    "success": false,
    "status": 400,
    "error": {
        "code": "bomb_found",
        "message": "No explosives allowed in this request."
    }
}
```

***
_Notice how a `message` field was added inside the `error` field._
***

There will in most cases only be one error message per error. However, validation errors are an exception to this rule. Since there can be multiple error messages after validation, all messages are put inside a `messages` field, instead of the singular `message` field.

Below is an example response from a user registration request, where multiple validation rules failed:

```json
{
    "success": false,
    "status": 422,
    "error": {
        "code": "validation_failed",
        "messages": [
            "Username is required.",
            "Password must be at least 8 characters long.",
        ]
    }
}
```

#### Language File

Instead of adding the error messages on the fly when you create the error responses, you can instead use the `errors.php` language file. The file should be in your `resources/lang/en` folder if you [published package assets](#publishing-package-assets).

The default language file looks like this:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Error Message Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the Laravel Responder package.
    | When it generates error responses, it will search the messages array
    | below for any key matching the given error code for the response.
    |
    */

    'resource_not_found' => 'The requested resource does not exist.',
    'unauthorized' => 'You are not authorized for this request.',

];
```

***
_If you use Lumen, you need to create the `resources/lang/en/errors.php` file manually. Feel free to copy over the code above into your file._
***

These messages are for the default Laravel exceptions, thrown when a model is not found or authorization failed. To learn more about how to catch these exceptions you can read the next section on [exceptions](#exceptions).

The error messages keys map up to an error code. So if you add the following line to the language file...

```php
'bomb_found' => 'No explosives allowed in this request.',
```

...and return the following error response...

```php
return $this->errorResponse('bomb_found', 400);
```

...the JSON below will be generated:

```json
{
    "success": false,
    "status": 400,
    "error": {
        "code": "bomb_found",
        "message": "No explosives allowed in this request."
    }
}
```

### Exceptions

When something bad happens, you might prefer to throw an actual exception instead of using the `error()` method. And even if you don't, you might want the package to catch Laravel's own exceptions, to convert them to proper JSON error responses for your API. 

#### Handle Exceptions

If you let the package handle exceptions, the package will catch all exceptions that extend `Flugg\Responder\Exceptions\ApiException` and turn them into informative JSON error responses.

To let the package handle exceptions you need to add some code to `app/Exceptions/Handler.php`. You have two options: extend the package exceptions handler or use a trait and add a code snippet.

##### Option 1: Extend Package Handler

You may let the package handle your exceptions by extending the package exception handler instead of the Laravel one. 

To do so, replace the following import in your exceptions handler...

```php
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
```

...with this one:

```php
use Flugg\Responder\Exceptions\Handler as ExceptionHandler;
```

***
_Lumen uses a different base exception handler, and is incompatible with the package exception handler. Option 2, however, works with both Lumen and Laravel._
***

##### Option 2: Use Trait

There exists other packages where you also need to extend their exception handlers. Since you can not extend more than one class at once, this quickly turns problematic. Which is why we provide an alternative way of adding the handler.

Just add the `Flugg\Responder\Traits\HandlesApiErrors` trait to your exceptions handler, and add the following code before the `return parent::render( $request, $e );` in your render method:

```php
public function render($request, Exception $e)
{
    $this->transformExceptions($e);
    
    if ($e instanceof \Flugg\Responder\Exceptions\ApiException) {
        return $this->renderApiError($e);
    }

    return parent::render($request, $e);
}`
```

#### Catching Laravel Exceptions

Laravel throws a few exceptions when things go wrong. For instance, if no model could be found during route model binding, an `Illuminate\Database\Eloquent\ModelNotFoundException` exception will be thrown. This exception is handled by the package if you added the package exception handling, as explained in the previous section.

However, when validation or authorization fails, a more generic `Illuminate\Http\Exception\HttpResponseException` is thrown. Since this exception is thrown from multiple sources, the package wont be able to distinct a validation error from an authorization error.

Luckily, Laravel allows you to override the `failedValidation()` and `failedAuthorization()` methods in your request files to throw your own exception. The package has a trait, `Flugg\Responder\Traits\ThrowsApiErrors`, which does just that.

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

The exceptions thrown by this trait extends `Flugg\Responder\Exceptions\ApiException`, so they are picked up by the package exceptions handler.

***
_As discussed in the [Transformers](#converting-to-camel-case) section, you can also use the `Flugg\Responder\Traits\ConvertsParameters` trait in your base request class to convert incoming parameters to snake case._
***

#### Creating Custom Exceptions

The package provides a few exceptions to handle default Laravel exceptions. However, you may want to create your own exceptions to handle custom errors. You are free to create as many exceptions as you like, but if you want them to be automatically caught and converted to a JSON response by the package, they will need to extend `Flugg\Responder\Exceptions\ApiException`.

When creating exceptions that extend `Flugg\Responder\Exceptions\ApiException`, you will get access to two protected properties you can declare to set status code and error code. The package will use these properties when converting the exception to a JSON response. Below is an example exception:

```php
<?php

namespace App\Exceptions;

use Flugg\Responder\Exceptions\ApiException;

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
