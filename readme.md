# Laravel Responder

[![Latest Stable Version](https://poser.pugx.org/mangopixel/laravel-responder/v/stable?format=flat-square)](https://github.com/mangopixel/laravel-responder)
[![Packagist Downloads](https://img.shields.io/packagist/dt/mangopixel/laravel-responder.svg?style=flat-square)](https://packagist.org/packages/mangopixel/laravel-responder)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](license.md)
[![Build Status](https://img.shields.io/travis/mangopixel/laravel-responder/master.svg?style=flat-square)](https://travis-ci.org/mangopixel/laravel-responder)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/mangopixel/laravel-responder.svg?style=flat-square)](https://scrutinizer-ci.com/g/mangopixel/laravel-responder/?branch=master)

__Work in progress, do not use in production!__

## Requirements

This package requires:
- PHP 7.0+
- Laravel 5.0+

## Installation

Install the package through Composer:

```shell
composer require mangopixel/laravel-responder
```

After updating Composer, append the following service provider to the `providers` key in `config/app.php`:

```php
Mangopixel\Responder\ResponderServiceProvider::class
```

If you like facades you may also append the `ApiResponse` facade to the `aliases` key:

```php
'ApiResponse' => Mangopixel\Responder\Facades\ApiResponse::class
```

You may also publish the package configuration file using the following Artisan command:

```shell
php artisan vendor:publish --provider="Mangopixel\Responder\ResponderServiceProvider"
```

This will add a `responder.php` configuration file in your `config` folder you can use to customize how the package behaves. It will also publish an `errors.php` file inside your `lang/en` folder which is used to convert error codes to specific messages. You may publish only the configuration or language file using the `config` and `lang` tags respectively.

## Usage

...So why not use Fractal directly? Well, here is an example response using Fractal:

```php
 public function index()
 {
    $users = User::all();
    $fractal = new Manager();
    $resource = new Collection( $users->toArray(), new UserTransformer() );

    return response()->json( $fractal->createData( $resource )->toArray() );
 }
```

I admit, the fractal manager could be refactored away. You could also return the array directly, but as soon as you want to return a different status code than 200, you need to wrap it in `response()->json()` anyway.

The point is, we all get a little spoiled by Laravel's magic. Wouldn't it be sweet if the above could be rewritten as:

```php
public function index()
{
    $users = User::all();

    return $this->successResponse( $users )
}
```

Well, with this package you can! It will automatically call Fractal behind the scenes and calls the `UserTransformer` magically because you're returning a collection of `User` models. It will also create a Fractal resource or item depending on if you pass in a single model instance or a collection of models. Interested? Read on!

### Transformers

TTransformers are classes which only responsibility is to transform one set of data to another. You may use a transformer to cast your fields to a certain type or only return a limited set of fields back, ou could also create entirely new fields. Here is an example:

```php
<?php

namespace App\Transformer;

use App\User;
use Mangopixel\Responder\Transformer;

class BookTransformer extends Transformer
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

It basically gives you a way to abstract your database logic away from your API design. As you can see from the example above, we cast the id to an integer to make sure it's not returned as a string. We also create a new field `fullName` which concatenates `first_name` and `last_name` attributes from the model. Also notice how this allows us to convert fields to camelcase in your JSON responses.

#### Creating transformers

The package gives you an Artisan command you can use to quickly generate new transformers. After installing the package you may call the following Artisan command in the terminal:

```bash
php artisan make:transformer UserTransformer
```

This will create a new `UserTransformer.php` in a new `app/Transformers` folder.

It will automatically resolve what model the template should include from the name. For instance, in the example above the package will extract `User` from `UserTransformer` and assume the models live directly in the app folder (as per Laravel's default). This means the model inside the transformer will be `User\App`.

If you store your models somewhere else you may also use the `--model` option to specify model path:

```bash
php artisan make:transformer UserTransformer --model="App\Models\User"
```

Do note that the transformer class extends from the `Mangopixel\Responder\Transformer` abstract class, which again extends Fractal's `League\FractalTransformerAbstract`.

#### Mapping transformer to model

Remember what I said earlier about the package magically finding the correct transformer when you pass in a model to the `successResponse` method? Well, I wasn't completely honest with you.

You're not bound to placing the transformers inside the `app/Transformers` folder, you can place them wherever you want. So, we need a way to find the correct transformer. What if, instead of applying the transformer to the response each time you respond with a user resource, you can instead specify what transformer to use in your models:

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mangopixel\Responder\Contracts\Transformable;

class Fruit extends Model implements Transformable
{
    /**
     * The path to the transformer class.
     *
     * @return string|null
     */
    public static function transformer()
    {
        return \App\Transformers\UserTransformer::class;
    }
}
```

Here we implement a `Mangopixel\Responder\Contracts\Transformable` interface which requires you to create a transformer method which returns the path to the transformer.

Each model you want to pass into the `successResponse` method must implement the contract. If you don't want to transform a given method you may just return `null` from the `transformer` method:

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mangopixel\Responder\Contracts\Transformable;

class Fruit extends Model implements Transformable
{
    /**
     * The path to the transformer class.
     *
     * @return string|null
     */
    public static function transformer()
    {
        return null;
    }
}
```

The idea is, every model you return from your API should be transformable, wether you apply a transformer or not is up to you.

Now we know about transformers, but how do we use them in practice?

### Success responses

When a user makes a valid request to your API you will want to provide an informative response in return to let the user know the request succeeded. You usually want to uniform all these responses to make sure the structure of the response is the same for all requests. You may also want to transform your responses with a transformer to ensure all data returned from the API is of correct type.

#### Making success responses

There are multiple ways you can generate success responses with the package.

##### Using trait

One way to create JSON API responses is to use the `Mangopixel\Responder\Traits\RespondsWithJson` trait in your controllers. You may use the trait in your `app/Http/Controller.php` file to get access to the methods from all controllers.

The trait give you access to two methods: `successResponse` and `errorResponse`. Let's look closer at the `successResponse` trait, we will look more at error responses later on. To create a success response you may return the method from any controller method:

```php
public function create()
{
    $user = User::create( request()->all() );

    return $this->successResponse( $user, 201 );
}
```

Here we're creating a new user and returning the created user back to the user with a status code of 201 (created). The status code defaults to 200.

Since we're only operating with a single model instance, the package will create a new `League\Fractal\Resource\Item` instance behind the scenes. It will also serialize the response using the serializer provided in the configurations. Then it will return back the response as a `Illuminate\Http\JsonResponse`.

This means you can modify the response like you would to a regular `response()->json()` call:

```php
public function create()
{
    $user = User::create( request()->all() );

    return $this->successResponse( $user, 201 )->withHeaders( [ 'X-Example', 'value' ] );
}
```

##### Using facade

_Make sure you register the facade as explained in the installation guide above before you embark on this chapter._

An optional way to create responses is to use the `ApiResponse` facade, in good company of Laravel's own `Response` facade. Creating a successful response using the facade is just as easy as with the trait:

```php
public function create()
{
    $user = User::create( request()->all() );

    return ApiResponse::success( $user, 201 );
}
```

Make sure you also import the facade with `use ApiResponse` in the top. Also do note the only difference is the facade does not use suffix the method name with Response, the response will be identical and they call on the same method behind the scenes.

##### Using dependency injection

You may also access the API responder through dependency injection:

```php
public function create( Responder $responder )
{
    $user = User::create( request()->all() );

    return $responder->success( $user, 201 );
}
```

The responder injected here is the actual responder service which does all the hard work behind the scenes. Both the trait and facade uses this service internally.

You may of course also inject it in the constructor to have access to it from multiple methods:

```php
<?php

namespace App\Http\Controllers;

use App\User;
use Mangopixel\Responder\Contract\Responder;

class TestsController extends Controller
{
    protected $responder;

    public function __construct( Responder $responder ) {
        $this->responder = $responder;
    }

    public function create()
    {
        $user = User::create( request()->all() );

        return $this->responder->success( $user, 201 );
    }
}

```

The service can also be retrieved from the service container using the `Mangopixel\Responder\Contract\Responder` contract:

```php
public function create()
{
    $user = User::create( request()->all() );

    return app( Responder::class )->success( $user, 201 );
}
```

However, whichever option you choose, I suggest you to stick to one for consistency sake.

#### Serializing responses

All success responses will be serialized using the same serializer to make sure all responses from your API remain in the same style.

All serializing is done through Fractal and it provides three serializers you can use out of the box. We've also created our own opinionated serializer which is the default serializer used. You can change which serializer you want to use through the `serializer` key in the configurations.

##### Default serializer

Let's take a look at how an example response with our `Mangopixel\Responder\Serializers\ApiSerializer` looks like:

```json
{
    "success": true,
    "status": 200,
    "data": {
        "id': 1,
        "email': 'example@email.com',
        'fullName': 'John Doe'
    }
}
```

##### Array serializer

Let's take a look at the simplest serializer from Fractal, `League\Fractal\Serializer\ArraySerializer`:

```json
{
    'id': 1,
    'email': 'example@email.com',
    'fullName': 'John Doe'
}
```

As you see this is the same as what you would normally get from a JSON response in Laravel. However, putting the data in its own `data` field can be useful when we want to attach meta data.

##### Data array serializer

A slightly more advanced serializer from Fractal is the `League\Fractal\Serializer\DataArraySerializer`:

```json
{
    "data": {
        "id": 1,
        "email": "example@email.com",
        "fullName": "John Doe"
    }
}
```

It"s basically the same as the array serializer, but with data in its own field.

##### JSON API serializer

The `League\Fractal\Serializer\JsonApiSerializer` serializer is the most sophisticated serializer from Fractal and follows the JSON-API standard:

```json
{
    "data": {
        "type": "users",
        "id": 1,
        "attributes": {
            "email": "example@email.com",
            "fullName": "John Doe"
        },
    }
}
```

##### Creating a custom serializer

If you want to create a custom serializer or find out more about the Fractal serializers you can read more at [http://fractal.thephpleague.com/serializers/](http://fractal.thephpleague.com/serializers/). Just make sure to set the `serializer` key to your custom serializer in the configuration and the rest should go automatically:

```php
'serializer' => App\Serializers\CustomSerializer::class
```

### Error responses

Just like you can generate success responses on the fly, you may of course also generate some error responses. They shouldn't happen, but they do, let's figure out how to best handle them.

#### Making error responses

Just like with success responses above you have multiple choices when it comes to generating error responses.

##### Using trait

As with success reponses you may use the `Mangopixel\Responder\Traits\RespondsWithJson` trait in your `app/Http/Controller.php` file to get access to the `errorResponse` method:

```php
public function index()
{
    if ( response()->has( 'bomb ) ) {
        return $this->errorResponse( 'bomb_found', 400 );
    }
}
```

Here we're checking if the response has an input named 'bomb', if so we abort with an error code of 'bomb_found' and status code of `400 (Bad Request)`.

The example above will return the following JSON response:

```json
{
    "success": false,
    "status": 400,
    "error": {
        "code": "bomb_found"
    }
}
```

Why do we have an `error` field with just a single field `code`? The `error` field will also include a `message` field if any message corresponding to the error code is found. More on this later.

##### Using facade

If you have registered the `ApiResponse` facade and follow the ways of the facades, you may also return an error response in the following way:

```php
public function index()
{
    if ( response()->has( 'bomb ) ) {
        return ApiResponse::error( 'bomb_found', 400 );
    }
}
```

Make sure you also import the facade with `use ApiResponse` in the top.

##### Using dependency injection

As with success responses, you may also access the API responder through dependency injection to make error responses:

```php
public function index()
{
    if ( response()->has( 'bomb ) ) {
        return $responder->error( 'bomb_found', 400 );
    }
}
```

Just like shown in the examples with the success responses above you can also inject it through the constructor or resolve it from the service container.

#### Adding error messages

An error code is useful for many reasons, but it might not give enough clues to the user about what the error is. Therefore you might want to add a more descriptive error message to the response, you can pass along a message as the third parameter:

```php
public function create()
{
    if ( response()->has( 'bomb ) ) {
        return $this->errorResponse( 'bomb_found', 400, 'No bombs allowed in this request' );
    }
}
```

The JSON response will then be the following:

```json
{
    "success": false,
    "status": 400,
    "error": {
        "code": "bomb_found",
        "message": "No bombs allowed in this request."
    }
}
```

There will usually only be one message per error, however, validation errors are an exception to this rule. As there can be multiple error messages after validation all messages are put inside a `messages` field, note the plural form. An example:

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

##### Language file

Instead of writing the error messages on the fly when you create the response, you can use the `errors.php` language file which should be in your `resources/lang/en` folder if you published vendor assets. The default language file looks like this:

```php
<?php

return [

    'resource_not_found' => 'The requested resource does not exist.',
    'unauthorized' => 'You are not authorized for this request.',

];
```

These messages are for the default Laravel exceptions thrown when a model is not found or authorization failed. To learn more about how to catch these exceptions you can read the next chapter on exception handling.

The error messages keys map up to an error code, so if you add the following line to the language file:

```php
'bomb_found' => 'No bombs allowed in this request.',
````

And create the following error response:

```php
return $this->errorResponse( 'bomb_found', 400 );
````

It will return the same JSON as above:

```json
{
    "success": false,
    "status": 400,
    "error": {
        "code": "bomb_found",
        "message": "No bombs allowed in this request."
    }
}
```

### Exception handling

When errors occour in your code you might prefer to throw an exception instead of using the `errorResponse` method. Even if you prefer the `errorResponse` method, you might be interested in letting the package catch exceptions thrown by Laravel and convert these to JSON responses. If so, read on!

#### Catching exceptions

To allow the package to catch exceptions to convert them to JSON error responses we need to add some code to your application exception handler found in `app/Exceptions/Handler.php`. There are two ways you can let the package handle exceptions.

##### Extending exception handler

You may let the package handle your exceptions by extending the package exception handler instead of the Laravel one. So, basically, make the following change to`app/Exceptions/Handler.php`:

```php
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
```

With:

```php
use Mangopixel\Responder\Exceptions\Handler as ExceptionHandler;
```

The package exception handler extends Laravel's exception handler.

##### Using trait

There are other packages where you need to extend their exception handler in order for the package to work. Which is why we provide an alternative way of adding the handler.

Just add the `Mangopixel\Responder\Traits\HandlesApiErrors` trait to your `app/Exceptions/Handler.php` file, and add the following code in the `render` method:

```php
public function render( $request, Exception $e )
{
    if ( $e instanceof ApiException ) {
        return $this->renderApiErrors( $e );
    }

    return parent::render( $request, $e );
}`
```

Make sure to import `ApiException` in the top: `use Mangopixel\Responder\Exceptions\ApiException`.

That's all you need to do, now the package will catch all exceptions that extend `ApiException` and convert them to JSON responses. It will also catch some of the Laravel's built in Exceptions and convert them to package exceptions that extend `ApiException`, you can see a list of which exceptions further down.

#### Creating your own exceptions



### Extension

#### Customizing the error response

There is currently no similar idea to error responses as there is with serializers to success responses. However, you can customize the error response by creating your own `Responder` service that extends `Mangopixel\Responder\Responder` and binding it to the service container:

```php
$this->app->bind( \Mangopixel\Responder\Contracts\Responder::class, \App\Services\CustomResponder::class );
```

A good file to place this would be in your `app/Providers/AppServiceProvider.php`.

The custom responder service would then need to override the `getErrorResponse` method:


```php
<?php

namespace App\Services;

use Mangopixel\Responder\Responder as BaseResponder;

class CustomResponder extends BaseResponder
{
    /**
     * Get the skeleton for an error response.
     *
     * @param string $errorCode
     * @param int    $statusCode
     * @return array
     */
    private function getErrorResponse( string $errorCode, int $statusCode ):array
    {
        return [
            'success' => false,
            'status' => $statusCode,
            'error' => [
                'code' => $errorCode
            ]
        ];
    }
}
```

You may change it whatever you like, however, the error message expects an `error` field to add messages to.

## License

Laravel Responder is free software distributed under the terms of the MIT license.
