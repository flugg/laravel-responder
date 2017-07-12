<p align="center"><img src="http://designiack.no/package-logo.png" width="396" height="111"></p>

<p align="center">
    <a href="https://github.com/flugger/laravel-responder"><img src="https://poser.pugx.org/flugger/laravel-responder/v/stable?format=flat-square" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/flugger/laravel-responder"><img src="https://img.shields.io/packagist/dt/flugger/laravel-responder.svg?style=flat-square" alt="Packagist Downloads"></a>
    <a href="license.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://travis-ci.org/flugger/laravel-responder"><img src="https://img.shields.io/travis/flugger/laravel-responder/master.svg?style=flat-square" alt="Build Status"></a>
    <a href="https://scrutinizer-ci.com/g/flugger/laravel-responder/?branch=master"><img src="https://img.shields.io/scrutinizer/g/flugger/laravel-responder.svg?style=flat-square" alt="Code Quality"></a>
</p>

Laravel Responder is a package for your JSON API, integrating [Fractal](https://github.com/thephpleague/fractal) into Laravel and Lumen. It can transform and serialize your success responses, as well as help you create error responses, handle exceptions and integration test your responses.

# Table of Contents

- [Philosophy](#philosophy)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Creating Responses](#creating-responses)
    - [Creating Success Responses](#creating-success-responses)
    - [Transforming Data](#transforming-data)
    - [Relationships](#relationships)
    - [Pagination & Cursors](#pagination-&-cursors)
    - [Serializers](#serializers)
    - [Creating Error Responses](#creating-error-responses)
    - [Handling Exceptions](#handling-exceptions)
    - [Testing](#testing)
- [Configuration](#configuration)
- [Contributing](#contributing)
- [License](#license)

# Philosophy

A good API should return consistent data, and coupling the response data to the database makes it harder to update the code in the future. Adding a transformer layer over the controller, transforming the data before the response is returned, gives us a consistent way of handling the response data. 

Since Laravel doesn't provide a transformation layer out of the box, we would have to use something like Fractal. Fractal solves some of the shortcomings of Laravel, however, it's often a bit cumbersome to integrate into the framework:

```php
 public function index()
 {
    $manager = new Manager();
    $resource = new Collection(User::get(), new UserTransformer(), 'users');

    return response()->json($manager->createData($resource)->toArray());
 }
```

Admittely, the Fractal manager could be moved outside the controller and the array could be returned directly. However, as soon as you want a different status code than the default `200`, you will probably want to wrap it in a `response()->json` call anyway.

The point is, we all get a little spoiled by Laravel's magic. Wouldn't it be sweet if the above could be rewritten as following:

```php
public function index()
{
    return responder()->success(User::all());
}
```

The package will call on Fractal behind the scenes to automatically transform and serialize the data. No longer will you have to instantiate different Fractal resources depending on if it's a model or a collection, the package deals with all of it automatically under the hood.

# Requirements

This package requires:
- PHP __7.0__+
- Laravel __5.1__+ or Lumen __5.1__+

# Installation

Install the package through Composer:

```shell
composer require flugger/laravel-responder
```

## Laravel

#### Register Service Provider

After updating Composer, append the following service provider to the `providers` key in `config/app.php`:

```php
Flugg\Responder\ResponderServiceProvider::class
```

#### Register Facade _(optional)_

If you like facades, you may also append the `Responder` facade to the `aliases` key:

```php
'Responder' => Flugg\Responder\Facades\Responder::class
```

#### Publish Package Assets _(optional)_

You may also publish the package configuration and language file using the Artisan command:

```shell
php artisan vendor:publish --provider="Flugg\Responder\ResponderServiceProvider"
```

This will publish a `responder.php` configuration file in your `config` folder. It will also publish an `errors.php` file inside your `lang/en` folder which is used to store your error messages.

## Lumen

#### Register Service Provider

Register the package service provider by adding the following line to `app/bootstrap.php`:

```php
$app->register(Flugg\Responder\ResponderServiceProvider::class);
```

#### Register Facade

You may also add the following line to `app/bootstrap.php` to register the optional facade:

```php
class_alias(Flugg\Responder\Facades\Responder::class, 'Responder');
```

#### Publish Package Assets

There is no `php artisan vendor:publish` in Lumen, you will therefore have to create your own `config/responder.php` file, if you want to configure the package. Do also note that unlike Laravel there is no `resources/lang` folder, however, you're free to create a `resources/lang/en/errors.php` manually and it will be picked up by the package.

# Usage

This documentation assumes some knowledge of how [Fractal](https://github.com/thephpleague/fractal) works.

## Creating Responses

The package provides a `Flugg\Responder\Responder` service class you can use to build responses for your API. The service has a `success` and `error` method to quickly whip up success- and error responses respectively.

### Accessing The Responder

Before you can begin creating responses, you need to retrieve an instance of the responder service. In good Laravel spirit you have multiple ways of achieving the same thing:

#### Option 1: Dependency Injection

You may inject the service directly into your controller:

```php
public function index(\Flugg\Responder\Responder $responder)
{
    return $responder->success();
}
```

You can also use the `error` method to create error responses:

```php
return $responder->error();
```

#### Option 2: Facade

Optionally, you may use the `Responder` facade to create responses:

```php
return Responder::success();
```
```php
return Responder::error();
```

#### Option 3: Helper Function

Additionally, if you're a fan of Laravel's `response` function, you may like the `responder` helper function:

```php
return responder()->success();
```
```php
return responder()->error();
```

#### Option 4: Trait

Lastly, the package also provides a `Flugg\Responder\Http\Controllers\CreatesApiResponses` trait you can use in your base controller. Just like the options above, the trait gives you access to two methods for creating responses:

```php
return $this->success();
```
```php
return $this->error();
```

***
_Which option you pick is up to you, they are all equivalent, the important thing is to stay consistent. We will use the helper function for the remaining of the documentation for simplicity's sake._
***

## Creating Success Responses

The easiest way to create a success response is to use the `success` method on the responder service, which returns an instance of `Illuminate\Http\JsonResponse`. You may attach data with your response by filling the first parameter:

```php
return responder()->success($user);
```

The status code is set to `200` by default, but can easily be changed by adding a second argument:

```php
return responder()->success($user, 201);
```

If you're not attaching any data to your response, you can omit the first parameter:

```php
return responder()->success(201);
```

You may additionally pass in meta data as the third argument:

```php
return responder()->success($user, 201, ['token' => 123]);
```

The status code may also be omitted if you want a default status code of `200`, but still want to include meta data:

```php
return responder()->success($user, ['token' => 123]);
```

### Using Response Builder

While the `success` method gives us a quick way of creating success responses, we sometimes want to customize our response even further. The `success` method take use of the `Flugg\Responder\Http\SuccessResponseBuilder` class behind the scenes to build success responses. You may utilize this class yourself by calling on the `transform` method on the responder service:

```php
return responder()->transform($user);
```

Unlike the `success` method, the  `transform` method returns an instance of `Flugg\Responder\SuccessResponseBuilder`, which provides a fluent interface for building success responses. 

Since this class implements the `Illuminate\Contracts\Support\Jsonable` contract, Laravel will know how to convert the above code to a valid response. However, we can explictly convert it to an instance of `Illuminate\Http\JsonResponse` and set status code with the `respond` method:

```php
return responder()->transform($user)->respond(201);
```

The `respond` method also accepts a list of headers for your response:

```php
return responder()->transform($user)->respond(201, ['x-foo' => true]);
```

Combining the `transform` and `respond` methods with the `addMeta` method, we can replicate the behavior of the `success` call from above:

```php
return responder()->transform($user)->addMeta(['token' => 123])->respond(201);
```

***
_In fact, this is exactly what the `success` method does behind the scenes._
***

The `addMeta` method can also be chained multiple times and may accept two arguments instead of an array:

```php
return responder()->transform()->addMeta('foo', 1)->addMeta('bar', 2)->respond();
```

You can explictly set a serializer using the `serializer` method:

```php
return responder()->transform()->serializer(new JsonApiSerializer)->respond();
```

The `serializer` method also accepts a class name string:

```php
return responder()->transform()->serializer(JsonApiSerializer::class)->respond();
```

Additonally, the `with` method can be used to include relations:

```php
return responder()->transform($user)->with('posts')->respond();
```

***
_The specified relations will be automatically eager loaded. However, instead of manually including relations using `with`, you can let the package do it automatically from the request, as described in the [Relationships](#relationships) chapter._
***

Instead of converting the response to an instance of `Illuminate\Http\JsonResponse` using the `respond` method, you may also cast it to a few other types:

```php
return responder()->transform($user)->toArray();
```
```php
return responder()->transform($user)->toCollection();
```
```php
return responder()->transform($user)->toJson();
```

## Transforming Data

When we send response data into the `success` and `transform` methods, the package will transform and serialize the data using Fractal. If we haven't set a transformer, like in the examples above, the package will create one on the fly using the model's `toArray` method. If the data doesn't contain a model, it will simply return the raw data.

### Setting Transformation Data

We've so far only sent a `$user` model into the `success` and `transform` methods as transformation data, however, there are a lots of other data types supported. You can find a list of all supported data types below.

#### Eloquent Models

As previously shown, we can transform Eloquent models:

```php
return responder()->success(User::first());
```

#### Collections & Arrays

You can also pass in a collection or array of Eloquent models:

```php
return responder()->success(User::get());
```
```php
return responder()->success(User::get()->all());
```

The data doesn't have to include models, and you may give it a collection or array of raw data:

```php
return responder()->success(collect(['foo' => 1]));
```
```php
return responder()->success(['foo' => 1]);
```

#### Paginators & Cursors

With big data sets, you may want to limit the results using a paginator:

```php
return responder()->success(User::paginate(30));
```

The package also supports cursor pagination by providing a `paginateByCursor` method:

```php
return responder()->success(User::paginateByCursor(30));
```

***
_Curious how this method work? We'll look into it in the [Pagination & Cursors](#pagination-&-cursors) chapter below._
***

#### Query Builders & Relations

You may additionally pass in a query builder instance:

```php
return responder()->success(User::where('id', 1));
```

Or a relation instance, like `\Illuminate\Database\Eloquent\Relations\HasMany`, which behaves like a query builder:

```php
return responder()->success(User::first()->permissions());
```

In both cases, the `get` method will be called behind the scenes to convert them to collections.

***
_In the examples above, we're using the `success` method, but we could just as well have used the `transform` method._
***

### Setting Transformers

We've looked at the different types of data that may be transformed, let's take a look at how we can set a transformer to perform the transformation.

#### Implicit Transformer Binding

In many cases you'll have multiple endpoints returning the same model, and these are often transformed using the same transformer. Instead of repeatedly setting the transformer for every response, we can bind a transformer to an Eloquent model. If the transformation data contains a model, the package will try to resolve a transformer from it.

We can set a transformer binding on our models by implementing the `Flugg\Responder\Contracts\Transformable` contract.

```php
class Post extends Model implements Transformable {}
```

We can satisfy the contract by implementing a static `transformer` method in our model. This method should return a transformer, which may be a simple closure returning the transformed array:

```php
public static function transformer()
{
    return function ($post) {
        return [
            'id' => (int) $post->id,
        ];
    };
}
```

You can also return a dedicated transformer class extending `Flugg\Responder\Transformers\Transformer`, either as a new instance or class name string:

```php
public static function transformer()
{
    return new PostTransformer();
}
```
```php
public static function transformer()
{
    return PostTransformer::class;
}
```

***
_Using a class name string, the transformer will be resolved out from Laravel's IoC container, giving you automatic dependency injection in the transformer's constructor._
***

#### Using The Response Builder

Sometimes you may want to transform raw data, or override the transformer bound to the model. In these cases, you may explictly set a transformer using the response builder by providing a second argument to the `transform` method.

Just like with the implicit binding above, you may give the `transform` method a closure transformer:

```php
return responder()->transform(Post::first(), function ($post) {
    return [
        'id' => (int) $post->id,
    ];
})->respond();
```

Or a dedicated transformer class:

```php
return responder()->transform(Post::first(), new PostTransformer)->respond();
```
```php
return responder()->transform(Post::first(), PostTransformer::class)->respond();
```

### Creating Transformers

Let's see how we can build our own dedicated transformer class. The package conveniently provides a `make:transformer` Artisan command you can use to quickly whip up new transformers:

```shell
php artisan make:transformer UserTransformer
```

#### Model Transformers

The above command will create a new `UserTransformer.php` file in the `app/Transformers` folder:

```php
<?php

namespace App\Transformers;

use App\User;
use Flugg\Responder\Transformer;

class UserTransformer extends Transformer
{
    /**
     * List of available relations.
     *
     * @var array
     */
    protected $relations = ['*'];

    /**
     * List of auto-included relations.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Transform the model.
     *
     * @param  User $user
     * @return array
     */
    public function transform(User $user):array
    {
        return [
            'id' => (int) $user->id,
        ];
    }
}
```

It will automatically resolve what model to inject from the name. For instance, in the example above the package will extract `User` from `UserTransformer` and assume the models live directly in the app folder (as per Laravel's convention).

If you store your models somewhere else you may also use the `--model` option to specify the model path:

```shell
php artisan make:transformer UserTransformer --model="App\Models\User"
```

#### Model Transformers With Pivot

You may optionally use the `--pivot` option to include an additional `transformPivot` method to transform a model's pivot table:

```shell
php artisan make:transformer UserTransformer --pivot
```

Whenever you transform a model that has a belongs to many relation included, the `transformPivot` will be called automatically with the corresponding `Illuminate\Database\Eloquent\Relations\Pivot` object:

```php
public function transformPivot(Pivot $pivot):array
{
    return [
        'user_id' => $pivot->user_id,
        'role_id' => $pivot->role_id,
    ];
}
```

#### Raw Transformers

You may also create a raw transformer, transforming an array instead of a model, using the `--raw` option:

```shell
php artisan make:transformer UserTransformer --raw
```

This will generate a lighter transformer, without the `$relations` property:

```php
<?php

namespace App\Transformers;

use Flugg\Responder\Transformer;

class DataTransformer extends Transformer
{
    /**
     * Transform the data.
     *
     * @param  array $data
     * @return array
     */
    public function transform(array $data):array
    {
        return [
            //
        ];
    }
}
```

### Transforming To Camel Case

Model attributes are traditionally specified in snake case, however, some of you might prefer to use camel case in your responses. A transformer makes for a perfect location to convert those attributes to camel case, like the `userId` field in the example below:

```php
return responder()->transform($post, function ($post) {
    return [
        'id' => (int) $post->id,
        'userId' => (int) $post->user_id,
    ];    
})->respond();
```

#### Transforming Requests To Snake Case

After responding with camel case, you probably want to let people send in request data using camel case parameters as well. The package provides a `Flugg\Responder\Http\Middleware\ConvertToSnakeCase` middleware you may append to the `$middleware` array in `app/Http/Kernel.php` to convert all request parameters to snake case automatically:

```php
protected $middleware = [
    // ...
    \Flugg\Responder\Http\Middleware\ConvertToSnakeCase::class,
];
```

## Relationships

### Including Relations

When transforming models, we sometimes want to include and transform related models as well.

#### From Request Parameter

The package will automatically include relations to the response data by parsing a query string parameter set in the configuration. By default the `include_relations_from_parameter` is set to `'with'`, allowing you to include relations using the set value as a request parameter:

```
GET /users?with=roles,posts.comments
```

In the request above, we ask for a list of all users _with_ related roles, posts, and the related comments of these posts.

#### Using The Response Builder

When the package automatically includes relations from a parameter or eager loads, it uses the `with` method on the response builder internally. However, you may use this method yourself to manually include relations:

```php
return responder()->transform(User::first())->with('roles', 'posts.comments')->respond();
```

***
_Using the `with` method is the equivalent of using `parseIncludes` with Fractal only._
***

#### From Eager Loads

You may also let the package include any relations eager loaded on the model by setting the `include_eager_loads` key in the configuration to `true`, allowing you to include relations purely from the transformation data:

```php
return responder()->success(User::with('roles', 'posts.comments')->first());
```

### Setting Relations

All model transformers generated through the `make:transformer` command will include a `$relations` and `$with` property. The `$relations` property specifies a list of available relations for the request, and works like Fractal's `$availableIncludes`. The small difference is that `$relations` allows a wildcard for allowing all relations:

```php
protected $relations = ['*'];
```

***
_**Security warning:** Since the package doesn't know what relations exists on a model unless you specify it in `$relations`, you're technically allowing  calls to any method on your model when using a wildcard. You should therefore always explictly list relations in `$relations` if the API is public available._
***

#### Setting Default Relations

Sometimes we want to always include some relations for a given resource. This can traditionally be achieved in Fratal using the `$defaultIncludes`, however, you will end up with the N+1 problem when including default relations from a nested transformer 

## Pagination & Cursors

## Serializers

## Creating Error Responses

## Handling Exceptions

# Configuration

If you've published vendor assets as described in the [installation guide](#installation), you will have access to a `responder.php` file in you `config` folder. You may update this file to change how the package should operate. We'll go through each configuration key.

#### Serializer Class Path

This key represents the full class path to the serializer class you would like the package to use when generating successful JSON responses. You may leave it with the default `Flugg\Responder\Serializers\ApiSerializer`, change it to one of [Fractal's serializers](http://fractal.thephpleague.com/serializers/), or create a [custom one yourself](#custom-serializers).

#### Include Status Code

The package will include a status code for both success- and error responses. You can disable this by setting this key to `false`.

# Contributing

Contributions are more than welcome and you're free to create a pull request on Github. You can run tests with the following command:

```shell
vendor/bin/phpunit
```

If you find bugs or have suggestions for improvements, feel free to submit an issue on Github. However, if it's a security related issue, please send an email to flugged@gmail.com instead.

# License

Laravel Responder is free software distributed under the terms of the MIT license. See [license.md](license.md) for more details.
