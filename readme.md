# Laravel Responder

[![Latest Stable Version](https://poser.pugx.org/mangopixel/laravel-responder/v/stable?format=flat-square)](https://github.com/mangopixel/laravel-responder)
[![Packagist Downloads](https://img.shields.io/packagist/dt/mangopixel/laravel-responder.svg?style=flat-square)](https://packagist.org/packages/mangopixel/laravel-responder)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](license.md)
[![Build Status](https://img.shields.io/travis/mangopixel/laravel-responder/master.svg?style=flat-square)](https://travis-ci.org/mangopixel/laravel-responder)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/mangopixel/laravel-responder.svg?style=flat-square)](https://scrutinizer-ci.com/g/mangopixel/laravel-responder/?branch=master)

__Work in progress, do not use in production!__

Laravel Responder is a package that integrates [Fractal](https://github.com/thephpleague/fractal) into Laravel. It will automatically transform your Eloquent models and serialize your API responses using a simple and elegant syntax. You can use it to send both success and error responses, and it gives you tools to handle exceptions and integration test your responses.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
    - [Registering Service Provider]()
    - [Registering Facade]()
    - [Publishing Package Assets]()
- [Philosophy](#success-responses)
- [Usage](#usage)
    - [Accessing the Responder](#accessing-the-responder)
        - [Using Dependency Injection](#using-dependency-injection)
        - [Using the Facade](#using-the-facade)
        - [Using the Helper Method](#using-the-helper-method)
        - [Using the Trait](#using-the-trait)
    - [Success Responses](#success-responses)
        - [Setting Status Codes](#setting-status-codes)
        - [Relationships](#relationships)
        - [Adding Meta Data](#adding-meta-data)
        - [Pagination](#pagination)
        - [Cursors](#cursors)
    - [Transformers](#transformers)
        - [Creating Transformers](#creating-transformers)
        - [Mapping Transformers to Models](#mapping-transformers-to-models)
        - [Converting to Camel Case](#convert-to-camel-case)
    - [Serializers](#transformers)
        - [Default Serializer](#default-serializer)
        - [Fractal Serializers](#fractal-serializers)
        - [Custom Serializers](#custom-serializers)
        - [Including Status Code](#including-status-code)
    - [Error Responses](#error-responses)
        - [Setting Status Codes](#setting-status-codes)
        - [Setting Error Messages](#setting-error-messages)
        - [Using Language File](#using-language-file)
    - [Exception Handling](#exception-handling)
        - [Extending the Handler](#extending-the-handler)
        - [Using the Trait](#using-trait)
        - [Catching Laravel Exceptions](#creating-custon-exceptions)
        - [Creating Custom Exceptions](#creating-custon-exceptions)
    - [Testing Helpers](#testing-helpers)
- [Configuration](#installation)
- [Extension](#extension)
    - [Customizing error responses](#customizing error responses)
- [Contributing](#contributing)
- [License](#license)

## Requirements

This package requires:
- PHP __7.0__+
- Laravel __5.0__+

## Installation

Install the package through Composer:

```shell
composer require flugg/laravel-responder
```

#### Registering Service Provider

After updating Composer, append the following service provider to the `providers` key in `config/app.php`:

```php
Flugg\Responder\ResponderServiceProvider::class
```

#### Registering Facade

If you like facades you may also append the `ApiResponse` facade to the `aliases` key:

```php
'ApiResponse' => Flugg\Responder\Facades\ApiResponse::class
```

#### Publishing Package Assets

You should also publish the package configuration and language file using the Artisan command:

```shell
php artisan vendor:publish
```

This will publish a `responder.php` configuration file in your `config` folder. 

It will also publish an `errors.php` file inside your `lang/en` folder which is used to store your error messages.

## Usage

The package has a `Flugg\Responder\Responder` service class which is responsible for generating success and error JSON responses for your API. The service has a `success()` and `error()` method which returns an instance of `Illuminate\Http\JsonResponse`.

### Accessing the Responder

To begin creating API responses, you need to access the responder service. In good Laravel spirit you have multiple ways of doing the same thing.

#### Inject the Responder

You may inject the service directly into your controller to create success responses:

```php
public function index( Responder $responder )
{
    $users = User::all();
    
    return $responder->success( $users );
}
```

You may also create error responses:

```php
return $responder->error( 'invalid_user' );
```

#### Using the Facade

Optionally, you may use the `ApiResponse` facade to create responses:

```php
return Responder::success( $users );
```
```php
return Responder::error( 'invalid_user' );
```

#### Using the Helper Method

You can also use the `responder()` helper method if you're fan of Laravel's `response()` helper method:

```php
return responder()->success( $users );
```
```php
return responder()->error( 'invalid_user' );
```

Both the helper method and the facade are just different ways of accessing the responder service, so you have access to the same methods.

#### Using the Trait

The package also has a `Flugg\Responder\Traits\RespondsWithJson` trait you can use in your base controller.

The trait gives you access to `successResponse()` and `errorResponse()` methods in your controllers: 

```php
return $this->successResponse( $users );
```
```php
return $this->errorResponse( 'invalid_user' );
```

These methods call on the service behind the scene.

_As described above, you may generate responses in multiple ways. Which way you choose is up to you, the important thing is to stay consistent. We will use the facade for the remaining of the documentation for simplicity sake._

### Success Responses

When a user makes a valid request to your API you probably want to provide an informative response in return to let the user know the request succeeded. You may use the `success()` method for this:

```php
public function index()
{
    $users = User::all();
    
    return Responder::success( $users );
}
```

__Note:__ If you try to run the above code you will get an exception saying the given model is not transformable. This is because all models you pass into the `success()` method must implement the `Flugg\Responder\Contracts\Transformable` contract and have a corresponding transformer. More on this in the [Transformers section](#transformers).

#### Setting Status Codes

The status code is `200` by default, but can easily be changed by adding an optional second argument to the `success()` method:

```php
return Responder::success( $user, 201 );
```

Sometimes you may not want to return anything, but still notify the user that the request was successful. In that case you may pass in the status code as the first argument and omit the data parameter:

```php
return Responder::success( 204 );
```

#### Relationships

Using Fractal you can include relationships to your response using the `parseIncludes()` method on the manager instance and add the available relationship as an `$availableIncludes` array in your transformers.

With Laravel Responder you don't have to do any of these things. It integrates neatly with Eloquent and automatically parses relationships:

```php
public function index()
{
    $users = User::with( 'profile', 'roles.permissions' )->all();
    
    return Responder::success( $users );
}
```

#### Adding Meta Data

You may want to pass in additional data to the response, you may do so by adding an additional third argument:

```php
return Responder::success( $user, 200, [ 'foo' => 'bar' ] );
```

You may also omit the status code if you want to send a default `200` response:

```php
return Responder::success( $user, [ 'foo' => 'bar' ] );
```

You may even omit the data parameter if you pass in a status code as the first argument:

```php
return Responder::success( 204, [ 'foo' => 'bar' ] );
```

#### Pagination

Adding pagination to your responses is equally easy. You can simply use Laravel's `paginate()` method on the query builder:

```php
public function index()
{
    $users = User::paginate( 15 );
    
    return Responder::success( $users );
}
```

The package will then automatically add info about the paginated results in the response data depending on which [serializer](#serializer) you use.

#### Cursors

__TODO__

### Transformers

Transformers are classes which only responsibility is to transform one set of data to another. Laravel Responder provides its own abstract transformer `Flugg\Responder\Transformer`. This transformer extends Fractal's `League\Fractal\Transformer` and adds integration with Eloquent. 

Your transformers should extend the package transformer as follow:

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
    public function transform( User $user )
    {
        return [
            'id'       => (int) $user->id,
            'email'    => $user->email,
            'fullName' => $user->first_name . ' ' . $user->last_name
        ];
    }
}
```

Transformers basically give you a way to abstract your database logic from your API design, and _transforms_ all values to the correct type. As seen in the example above, we cast the user id to an integer and concatenate the first and last name together and only expose a `fullName` field to the API.

Also note how we're converting snake case fields to camel case. You can read more about it in the [Converting to Camel Case section]().

#### Creating Transformers

The package gives you an Artisan command you can use to quickly generate new transformers:

```bash
php artisan make:transformer UserTransformer
```

This will create a new `UserTransformer.php` in an `app/Transformers` folder.

It will automatically resolve what model to inject from the name. For instance, in the example above the package will extract `User` from `UserTransformer` and assume the models live directly in the app folder (as per Laravel's default).

If you store your models somewhere else you may also use the `--model` option to specify model path:

```bash
php artisan make:transformer UserTransformer --model="App\Models\User"
```

#### Mapping Transformers to Models

When you pass in a model or a collection of models into the `success` method, the package will automatically transform the models. However, because you're free to place your transformers anywhere you want, the package has no way of knowing which transformer to use for each model. 

To map a transformer to a model, you need to implement `Flugg\Responder\Contract\Transformable` in your models. The interface requires you to create a static `transformer()` method, which should return the path to the corresponding transformer:

```php
<?php

namespace App;

use App\Transformers\FruitTransformer;
use Illuminate\Database\Eloquent\Model;
use Flugg\Responder\Contracts\Transformable;

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

You may want to expose all fields in your API in camel case. However, Eloquent uses snake case attributes by default. A transformer is one of the last things that take place before the data is returned to the API, and is a perfect location to do the conversion:

```php
public function transform( User $user )
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

This is great, but only works for API responses, not for request parameters. Imagine you create a user from the request input with camel case fields:

```php
User::create( request()->all() );
```

That wont work because the user model expects snake case fields. However, the package has a `Flugg\Responder\Traits\ConvertToSnakeCase` trait you can use in your `app/Http/Requests/Request.php` file to automatically convert all parameters to snake case:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mangopixel\Responder\Traits\ConvertToSnakeCase;

abstract class Request extends FormRequest
{
    use ConvertToSnakeCase;
}
```

### Serializers

After your models have been transformed, the data will be serialized using the serializer set in the configuration file. A serializer structures your data output in a certain way. It can also add additional data like pagination information or meta data.

When all responses are serialized with the same serializer, you end up with a consistent API, and if you want to change the structure in the future, you can simply change the serializer in the configurations.

#### Default Serializer

#### Fractal Serializers

#### Custom Serializers

#### Including Status Code

### Error Responses

#### Setting Status Codes

#### Setting Error Messages

#### Using Language File

### Exception Handling

#### Extending the Handler

#### Using the Trait

#### Catching Laravel Exceptions

#### Creating Custom Exceptions

### Testing Helpers

## Configuration

#### Serializer Class Path

The full class path to the serializer class you would like the package to use when generating successful JSON responses. You may change it to one of Fractal's serializers or create a custom one yourself.

#### Include Status Code

Wether or not you want to include status codes in your JSON responses. You may choose to include it for error responses, success responses or both, just by changing the configuration values listed below.

## Extension

#### Customizing error responses

## Contribution

Contributions are more than welcome and you're free to create a pull request on Github. Please see [contributing.md]() for more details.

If you find bugs or have suggestions for improvements, feel free to submit an issue on Github. However, if the issue is a security related issue, please send an email to [flugged@gmail.com]() instead.

## License

Laravel Responder is free software distributed under the terms of the MIT license. See [license.md]() for more details.
