<p align="center"><img src="https://user-images.githubusercontent.com/1271812/111231999-824f2b00-85ea-11eb-9afc-c0176529a234.png" width="500px"></p>

<p align="center">
    <a href="https://github.com/flugger/laravel-responder"><img src="https://poser.pugx.org/flugger/laravel-responder/v/stable?format=flat-square" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/flugger/laravel-responder"><img src="https://img.shields.io/packagist/dt/flugger/laravel-responder.svg?style=flat-square" alt="Packagist Downloads"></a>
    <a href="LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://travis-ci.org/flugger/laravel-responder"><img src="https://img.shields.io/travis/flugger/laravel-responder/master.svg?style=flat-square" alt="Build Status"></a>
    <a href="https://scrutinizer-ci.com/g/flugger/laravel-responder/?branch=master"><img src="https://img.shields.io/scrutinizer/g/flugger/laravel-responder.svg?style=flat-square" alt="Code Quality"></a>
    <a href="https://scrutinizer-ci.com/g/flugger/laravel-responder/code-structure/master"><img src="https://img.shields.io/scrutinizer/coverage/g/flugger/laravel-responder.svg?style=flat-square" alt="Test Coverage"></a>
    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=PRMC9WLJY8E46&lc=NO&item_name=Laravel%20Responder&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted"><img src="https://img.shields.io/badge/donate-PayPal-yellow.svg?style=flat-square" alt="Donate"></a>
</p>

Laravel Responder is a package for building API responses, integrating [Fractal](https://github.com/thephpleague/fractal) into Laravel and Lumen. It can transform your data using transformers, create and serialize success- and error responses, handle exceptions and assist you with testing your responses.

# Table of Contents

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Creating Responses](#creating-responses)
    - [Creating Success Responses](#creating-success-responses)
    - [Creating Transformers](#creating-transformers)
    - [Transforming Data](#transforming-data)
    - [Creating Error Responses](#creating-error-responses)
    - [Handling Exceptions](#handling-exceptions)
- [Contributing](#contributing)
- [Donating](#contributing)
- [License](#license)

# Introduction

Laravel lets you return models directly from a controller method to convert it to JSON. This is a quick way to build APIs but leaves your database columns exposed. [Fractal](https://fractal.thephpleague.com), a popular PHP package from [The PHP League](https://thephpleague.com/), solves this by introducing transformers. However, it can be a bit cumbersome to integrate into the framework as seen below:

```php
 public function index()
 {
    $resource = new Collection(User::all(), new UserTransformer());

    return response()->json((new Manager)->createData($resource)->toArray());
 }
```

Not _that_ bad, but we all get a little spoiled by Laravel's magic. Wouldn't it be better if we could refactor it to:

```php
public function index()
{
    return responder()->success(User::all())->respond();
}
```

The package will allow you to do this and much more. The goal has been to create a high-quality package that feels like native Laravel. A package that lets you embrace the power of Fractal, while hiding it behind beautiful abstractions. There has also been put a lot of focus and thought to the documentation. Happy exploration!

# Requirements

This package requires:
- PHP __7.0__+
- Laravel __5.1__+ or Lumen __5.1__+

# Installation

To get started, install the package through Composer:

```shell
composer require flugger/laravel-responder
```

## Laravel

#### Register Service Provider

Append the following line to the `providers` key in `config/app.php` to register the package:

```php
Flugg\Responder\ResponderServiceProvider::class,
```

***
_The package supports auto-discovery, so if you use Laravel 5.5 or later you may skip registering the service provider and facades as they will be registered automatically._
***

#### Register Facades _(optional)_

If you like facades, you may also append the `Responder` and `Transformation` facades to the `aliases` key:

```php
'Responder' => Flugg\Responder\Facades\Responder::class,
'Transformation' => Flugg\Responder\Facades\Transformation::class,
```

#### Publish Package Assets _(optional)_

You may additionally publish the package configuration and language file using the `vendor:publish` Artisan command:

```shell
php artisan vendor:publish --provider="Flugg\Responder\ResponderServiceProvider"
```

This will publish a `responder.php` configuration file in your `config` folder. It will also publish an `errors.php` file inside your `lang/en` folder which can be used for storing error messages.

## Lumen

#### Register Service Provider

Add the following line to `app/bootstrap.php` to register the package:

```php
$app->register(Flugg\Responder\ResponderServiceProvider::class);
```

#### Register Facades _(optional)_

You may also add the following lines to `app/bootstrap.php` to register the facades:

```php
class_alias(Flugg\Responder\Facades\Responder::class, 'Responder');
class_alias(Flugg\Responder\Facades\Transformation::class, 'Transformation');
```

#### Publish Package Assets _(optional)_

Seeing there is no `vendor:publish` command in Lumen, you will have to create your own `config/responder.php` file if you want to configure the package.

# Usage

This documentation assumes some knowledge of how [Fractal](https://github.com/thephpleague/fractal) works.

## Creating Responses

The package has a `Responder` service class, which has a `success` and `error` method to build success- and error responses respectively. To use the service and begin creating responses, pick one of the options below:

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

#### Option 4: Use `MakesResponses` Trait

Lastly, the package provides a `Flugg\Responder\Http\MakesResponses` trait you can use in your controllers:

```php
return $this->success();
```

```php
return $this->error();
```

***
_Which option you pick is up to you, they are all equivalent, the important thing is to stay consistent. The helper function (option 2) will be used for the remaining of the documentation._
***

### Building Responses

The `success` and `error` methods return a `SuccessResponseBuilder` and `ErrorResponseBuilder` respectively, which both extend an abstract `ResponseBuilder`, giving them common behaviors. They will be converted to JSON when returned from a controller, but you can explicitly create an instance of `Illuminate\Http\JsonResponse` with the `respond` method:

```php
return responder()->success()->respond();
```

```php
return responder()->error()->respond();
```

The status code is set to `200` by default, but can be changed by setting the first parameter. You can also pass a list of headers as the second argument:

```php
return responder()->success()->respond(201, ['x-foo' => true]);
```

```php
return responder()->error()->respond(404, ['x-foo' => false]);
```

***
_Consider always using the `respond` method for consistency's sake._
***

### Casting Response Data

Instead of converting the response to a `JsonResponse` using the `respond` method, you can cast the response data to a few other types, like an array:

```php
return responder()->success()->toArray();
```

```php
return responder()->error()->toArray();
```

You also have a `toCollection` and `toJson` method at your disposal.

### Decorating Response

A response decorator allows for last minute changes to the response before it's returned. The package comes with two response decorators out of the box adding a `status` and `success` field to the response output. The `decorators` key in the configuration file defines a list of all enabled response decorators:

```php
'decorators' => [
    \Flugg\Responder\Http\Responses\Decorators\StatusCodeDecorator::class,
    \Flugg\Responder\Http\Responses\Decorators\SuccessFlagDecorator::class,
],
```

You may disable a decorator by removing it from the list, or add your own decorator extending the abstract class `Flugg\Responder\Http\Responses\Decorators\ResponseDecorator`. You can also add additional decorators per response:

```php
return responder()->success()->decorator(ExampleDecorator::class)->respond();
```
```php
return responder()->error()->decorator(ExampleDecorator::class)->respond();
```

***

The package also ships with some situational decorators disabled by default, but which can be added to the decorator list: 
- `PrettyPrintDecorator` decorator will beautify the JSON output;

```php
\Flugg\Responder\Http\Responses\Decorators\PrettyPrintDecorator::class,
```

- `EscapeHtmlDecorator` decorator, based on the "sanitize input, escape output" concept, will escape HTML entities in all strings returned by your API. You can securely store input data "as is" (even malicious HTML tags) being sure that it will be outputted as un-harmful strings. Note that, using this decorator, printing data as text will result in the wrong representation and you must print it as HTML to retrieve the original value.

```php
\Flugg\Responder\Http\Responses\Decorators\EscapeHtmlDecorator::class,
```

***

## Creating Success Responses

As briefly demonstrated above, success responses are created using the `success` method:

```php
return responder()->success()->respond();
```

Assuming no changes have been made to the configuration, the above code would output the following JSON:

```json
{
    "status": 200,
    "success": true,
    "data": null
}
```

### Setting Response Data

The `success` method takes the response data as the first argument:

```php
return responder()->success(Product::all())->respond();
```

It accepts the same data types as you would normally return from your controllers, however, it also supports query builder and relationship instances:

```php
return responder()->success(Product::where('id', 1))->respond();
```

```php
return responder()->success(Product::first()->shipments())->respond();
```

***
_The package will run the queries and convert them to collections behind the scenes._
***

### Transforming Response Data

The response data will be transformed with Fractal if you've attached a transformer to the response. There are two ways to attach a transformer; either _explicitly_ by setting it on the response, or _implicitly_ by binding it to a model. Let's look at both ways in greater detail.

#### Setting Transformer On Response

You can attach a transformer to the response by sending a second argument to the `success` method. For instance, below we're attaching a simple closure transformer, transforming a list of products to only output their names:

```php
return responder()->success(Product::all(), function ($product) {
    return ['name' => $product->name];
})->respond();
```

You may also transform using a dedicated transformer class:

```php
return responder()->success(Product::all(), ProductTransformer::class)->respond();
```

```php
return responder()->success(Product::all(), new ProductTransformer)->respond();
```

***
_You can read more about creating dedicated transformer classes in the [Creating Transformers](#creating-transformers) chapter._
***

#### Binding Transformer To Model

If no transformer is set, the package will search the response data for an element implementing the `Flugg\Responder\Contracts\Transformable` interface to resolve a transformer from. You can take use of this by implementing the `Transformable` interface in your models:

```php
class Product extends Model implements Transformable {}
```

You can satisfy the contract by adding a `transformer` method that returns the corresponding transformer:

```php
/**
 * Get a transformer for the class.
 *
 * @return \Flugg\Responder\Transformers\Transformer|string|callable
 */
public function transformer()
{
    return ProductTransformer::class;
}
```

***
_You're not limited to returning a class name string, you can return a transformer instance or closure transformer, just like the second parameter of the `success` method._
***

Instead of implementing the `Transformable` contract for all models, an alternative approach is to bind the transformers using the `bind` method on the `TransformerResolver` class. You can place the code below within `AppServiceProvider` or an entirely new `TransformerServiceProvider`:

```php
use Flugg\Responder\Contracts\Transformers\TransformerResolver;

public function boot()
{
    $this->app->make(TransformerResolver::class)->bind([
        \App\Product::class => \App\Transformers\ProductTransformer::class,
        \App\Shipment::class => \App\Transformers\ShipmentTransformer::class,
    ]);
}
```

After you've bound a transformer to a model you can skip the second parameter and still transform the data:

```php
return responder()->success(Product::all())->respond();
```

***
_As you might have noticed, unlike Fractal, you don't need to worry about creating resource objects like `Item` and `Collection`. The package will make one for you based on the data type, however, you may wrap your data in a resource object to override this._
***

### Setting Resource Key

If the data you send into the response is a model or contains a list of models, a resource key will implicitly be resolved from the model's table name. You can overwrite this by adding a `getResourceKey` method to your model:

```php
public function getResourceKey(): string {
    return 'products';
}
```

You can also explicitly set a resource key on a response by sending a third argument to the ´success` method:

```php
return responder()->success(Product::all(), ProductTransformer::class, 'products')->respond();
```

### Paginating Response Data

Sending a paginator to the `success` method will set pagination meta data and transform the data automatically, as well as append any query string parameters to the paginator links.

```php
return responder()->success(Product::paginate())->respond();
```

Assuming there are no products and the default configuration is used, the JSON output would look like:

```json
{
    "success": true,
    "status": 200,
    "data": [],
    "pagination": {
        "total": 0,
        "count": 0,
        "perPage": 15,
        "currentPage": 1,
        "totalPages": 1,
        "links": []
    }
}
```

#### Setting Paginator On Response

Instead of sending a paginator as data, you may set the data and paginator seperately, like you traditionally would with Fractal. You can manually set a paginator using the `paginator` method, which expects an instance of `League\Fractal\Pagination\IlluminatePaginatorAdapter`:

```php
$paginator = Product::paginate();
$adapter = new IlluminatePaginatorAdapter($paginator);

return responder()->success($paginator->getCollection())->paginator($adapter)->respond();
```

#### Setting Cursor On Response

You can also set cursors using the `cursor` method, expecting an instance of `League\Fractal\Pagination\Cursor`:

```php
if ($request->has('cursor')) {
    $products = Product::where('id', '>', request()->cursor)->take(request()->limit)->get();
} else {
    $products = Product::take(request()->limit)->get();
}

$cursor = new Cursor(request()->cursor, request()->previous, $products->last()->id ?? null, Product::count());

return responder()->success($products)->cursor($cursor)->respond();
```

### Including Relationships

If a transformer class is attached to the response, you can include relationships using the `with` method:

```php
return responder()->success(Product::all())->with('shipments')->respond();
```

You can send multiple arguments and specify nested relations using dot notation:

```php
return responder()->success(Product::all())->with('shipments', 'orders.customer')->respond();
```

All relationships will be automatically eager loaded, and just like you would when using `with` or `load` to eager load with Eloquent, you may use a callback to specify additional query constraints. Like in the example below, where we're only including related shipments that hasn't yet been shipped:

```php
return responder()->success(Product::all())->with(['shipments' => function ($query) {
    $query->whereNull('shipped_at');
}])->respond();
```

#### Including From Query String

Relationships are loaded from a query string parameter if the `load_relations_parameter` configuration key is set to a string. By default, it's set to `with`, allowing you to automatically include relations from the query string:

```
GET /products?with=shipments,orders.customer
```

#### Excluding Default Relations

In your transformer classes, you may specify relations to automatically load. You may disable any of these relations using the `without` method:

```php
return responder()->success(Product::all())->without('comments')->respond();
```

### Filtering Transformed Data

The technique of filtering the transformed data to only return what we need is called sparse fieldsets and can be specified using the `only` method:

```php
return responder()->success(Product::all())->only('id', 'name')->respond();
```

When including relationships, you may also want to filter fields on related resources as well. This can be done by instead specifying an array where each key represents the resource keys for the resources being filtered

```php
return responder()->success(Product::all())->with('shipments')->only([
    'products' => ['id', 'name'],
    'shipments' => ['id']
])->respond();
```

#### Filtering From Query String

Fields will automatically be filtered if the `filter_fields_parameter` configuration key is set to a string. It defaults to `only`, allowing you to filter fields from the query string:

```
GET /products?only=id,name
```

You may automatically filter related resources by setting the parameter to a key-based array:

```
GET /products?with=shipments&only[products]=id,name&only[shipments]=id
```

### Adding Meta Data

You may want to attach additional meta data to your response. You can do this using the `meta` method:

```php
return responder()->success(Product::all())->meta(['count' => Product::count()])->respond();
```

When using the default serializer, the meta data will simply be appended to the response array:

```json
{
    "success": true,
    "status": 200,
    "data": [],
    "count": 0
}
```

### Serializing Response Data

After the data has been transformed, it will be serialized using the specified success serializer in the configuration file, which defaults to the package's own `Flugg\Responder\Serializers\SuccessSerializer`. You can overwrite this on your responses using the `serializer` method:

```php
return responder()->success()->serializer(JsonApiSerializer::class)->respond();
```

```php
return responder()->success()->serializer(new JsonApiSerializer())->respond();
```

Above we're using Fractal's `JsonApiSerializer` class. Fractal also ships with an `ArraySerializer` and `DataArraySerializer` class. If none of these suit your taste, feel free to create your own serializer by extending `League\Fractal\Serializer\SerializerAbstract`. You can read more about it in [Fractal's documentation](http://fractal.thephpleague.com/serializers/).

## Creating Transformers

A dedicated transformer class gives you a convenient location to transform data and allows you to reuse the transformer at multiple places. It also allows you to include and transform relationships. You can create a transformer using the `make:transformer` Artisan command:

```shell
php artisan make:transformer ProductTransformer
```

The command will generate a new `ProductTransformer.php` file in the `app/Transformers` folder:

```php
<?php

namespace App\Transformers;

use App\User;
use Flugg\Responder\Transformers\Transformer;

class ProductTransformer extends Transformer
{
    /**
     * List of available relations.
     *
     * @var string[]
     */
    protected $relations = [];

    /**
     * A list of autoloaded default relations.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Transform the model.
     *
     * @param  \App\Product $product
     * @return array
     */
    public function transform(Product $product): array
    {
        return [
            'id' => (int) $product->id,
        ];
    }
}
```

It will automatically resolve a model name from the name provided. For instance, the package will extract `Product` from `ProductTransformer` and assume the models live directly in the `app` folder (as per Laravel's convention). If you store them somewhere else, you can use the `--model` (or `-m`) option to override it: 

```shell
php artisan make:transformer ProductTransformer --model="App\Models\Product"
```

#### Creating Plain Transformers

The transformer file generated above is a model transformer expecting an `App\Product` model for the `transform` method. However, we can create a plain transformer by applying the `--plain` (or `-p`) modifier:

```shell
php artisan make:transformer ProductTransformer --plain
```

This will remove the typehint from the `transform` method and add less boilerplate code.

### Setting Relationships

The `$relations` and `$load` properties in the transformer are the equivalent to Fractal's own `$availableIncludes` and `$defaultIncludes`. In addition to the slight name change, the package uses the `$relations` and `$load` properties to map out all available relationships for eager loading, so in contrast to Fractal, you should map the relationship to their corresponding transformer:

```php
protected $relations = [
    'shipments' => ShipmentTransformer::class,
];
```

***
_You can choose to skip the mapping and just pass the strings like with Fractal, but that means the package wont be able to eager load relationships automatically._
***

#### Setting Whitelisted Relationships

The `$relations` property specifies a list of relations available to be included. You can set a list of relations mapped to their corresponding transformers:

```php
protected $relations = [
    'shipments' => ShipmentTransformer::class,
    'orders' => OrderTransformer::class,
];
```

#### Setting Autoloaded Relationships

The `$load` property specifies a list of relations to be autoloaded every time you transform data with the transformer:

```php
protected $load = [
    'shipments' => ShipmentTransformer::class,
    'orders' => OrderTransformer::class,
];
```

***
_You don't have to add relations to both `$relations` and `$load`, all relations in `$load` will be available by nature._
***

### Including Relationships

While Fractal requires you to to create a method in your transformer for every included relation, this package lets you skip this when transforming models, as it will automatically fetch relationships from the model. You may of course override this functionality by creating an "include" method:

```php
/**
 * Include related shipments.
 *
 * @param  \App\Product $product
 * @return mixed
 */
public function includeShipments(Product $product)
{
    return $product->shipments;
}
```

Unlike Fractal you can just return the data directly without wrapping it in an `item` or `collection` method.

***
_You should be careful with executing database calls inside the include methods as you might end up with an unexpected amount of hits to the database._
***

#### Using Include Parameters

Fractal can parse query string parameters which can be used when including relations. For more information about how to format the parameters see Fractal's [documentation on parameters](https://fractal.thephpleague.com/transformers/#include-parameters). You may access the parameters by adding a second parameter to the "include" method:

```php
public function includeShipments(Product $product, Collection $parameters)
{
    return $product->shipments->take($parameters->get('limit'));
}
```

***
_To be as decoupled from Fractal as possible the parameters (which are normally accessed using `League\Fractal\ParamBag`) are accessed as Laravel collections instead._
***

#### Adding Query Constraints

Just as you can specify a query constraint when including a relationship with the `with` method, you can also add query constraints as a "load" method on the transformer. This will automatically be applied when extracting relationships for eager loading.

```php
/**
 * Load shipments with constraints.
 *
 * @param  \Illuminate\Database\Eloquent\Builder $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function loadShipments($query)
{
    return $query->whereNull('shipped_at');
}
```

***
_Note: You cannot mix "include" and "load" methods because the package doesn't eager load relationships included with an "include" method._
***

### Filtering Relationships

After a relation has been included, you can make any last second changes to it using a filter method. For instance, below we're filtering the list of related shipments to only include shipments that has not been shipped:

```php
/**
 * Filter included shipments.
 *
 * @param  \Illuminate\Database\Eloquent\Collection $shipments
 * @return \Illuminate\Support\Collection
 */
public function filterShipments($shipments)
{
    return $shipments->filter(function ($shipment) {
        return is_null($shipment->shipped_at);
    });
}
```

## Transforming Data

We've looked at how to transform response data of success responses, however, there may be other places than your controllers where you want to transform data. An example is broadcasted events where you're exposing data using websockets instead of HTTP. You just want to return the transformed data, not an entire response. 

It's possible to simply transform data by newing up the transformer and calling `transform`:

```php
return (new ProductTransformer)->transform(Product::first());
```

However, this approach might become a bit messy when building transformations with relationships:

```php
return array_merge((new ProductTransformer)->transform($product = Product::first()), [
    'shipments' => $product->shipments->map(function ($shipment) {
        return (new ShipmentTransformer)->transform($shipment);
    })
]);
```

Yuck! Imagine that with multiple nested relationships. Let's explore a simpler way to handle this.

### Building Transformations

The `SuccessResponseBuilder` actually delegates all of the transformation work to a dedicated `Flugg\Responder\TransformBuilder` class. We can use this class ourself to transform data. For instance, if the product and shipment transformers were bound to the models, we could replicate the code above in the following way:

```php
public function index(TransformBuilder $transformation)
{
    return $transformation->resource(Product::all())->with('shipments')->transform();
}
```

Instead of using the `success` method on the `Responder` service, we use the `resource` method on the `TransformBuilder` with the same method signature. We also use `transform` to execute the transformation instead of `respond` as we did when creating responses. In addition to the `with` method, you also have access to the other transformation methods like `without`, `only`, `meta` and `serializer`.

***
_Using `toArray` on the `Responder` service is almost the same as the code above, however, it will also include response decorators which might not be desired._
***

### Transforming Without Serializing

When using the `TransformBuilder` to transform data it will still serialize the data using the configured serializer. Fractal requires the use of a serializer to transform data, but sometimes we're just interested in the raw transformed data. The package ships with a `Flugg\Responder\Serializers\NoopSerializer` to solve this, a no-op serializer which leaves the transformed data untouched:

```php
return $transformation->resource(Product::all())->serializer(NoopSerializer::class)->transform();
```

If you think this is looking messy, don't worry, there's a quicker way. In fact, you will probably never even need to utilize the `NoopSerializer` or `TransformBuilder` manually, but it helps to know how it works. The `Flugg\Responder\Transformation` is a class which can be used for quickly transforming data without serializing.

#### Option 1: The `Transformation` Service

The `Transformation` class utilizes the `TransformBuilder` class to build a transformation using the `NoopSerializer`. You can inject the `Transformation` class and call `make` to obtain an instance of `TransformBuilder` which gives you access to all of the chainable methods including `with`, like below:

```php
public function __construct(Transformation $transformation)
{
    $transformation->make(Product::all())->with('shipments')->transform();
}
```

#### Option 2: The `transformation` Helper

You can use the `transformation` helper function to transform data without serializing:

```php
transformation(Product::all())->with('shipments')->transform();
```

#### Option 3: The `Transformation` Facade

You can also use the `Transformation` facade to achieve the same thing:

```php
Transformation::make(Product::all())->with('shipments')->transform();
```

### Transforming To Camel Case

Model attributes are traditionally specified in snake case, however, you might prefer to use camel case for the response fields. A transformer makes for a perfect location to convert the fields, as seen from the `soldOut` field in the example below:

```php
return responder()->success(Product::all(), function ($product) {
    return ['soldOut' => (bool) $product->sold_out];    
})->respond();
```

#### Transforming Request Parameters

After responding with camel case, you probably want to let people send in request data using camel cased parameters as well. The package provides a `Flugg\Responder\Http\Middleware\ConvertToSnakeCase` middleware you can append to the `$middleware` array in `app/Http/Kernel.php` to convert all parameters to snake case automatically:

```php
protected $middleware = [
    // ...
    \Flugg\Responder\Http\Middleware\ConvertToSnakeCase::class,
];
```

***
_The middleware will run before request validation, so you should specify your validation rules in snake case as well._
***

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

***
_You may optionally use integers for error codes._
***

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

#### Register Messages Using `ErrorMessageResolver`

Instead of using language files, you may alternatively set error messages directly on the `ErrorMessageResolver` class. You can place the code below within `AppServiceProvider` or an entirely new `TransformerServiceProvider`:

```php
use Flugg\Responder\ErrorMessageResolver;

public function boot()
{
    $this->app->make(ErrorMessageResolver::class)->register([
        'sold_out_error' => 'The requested product is sold out.',
    ]);
}
```

### Adding Error Data

You may want to set additional data on the error response. Like in the example below, we're returning a list of shipments with the `sold_out` error response, giving the consumer information about when a new shipment for the product might arrive.

```php
return responder()->error('sold_out')->data(['shipments' => Shipment::all()])->respond();
```

The error data will be appended to the response data. Assuming we're using the default serializer and there are no shipments in the database, the code above would look like:

```json
{
    "success": false,
    "status": 500,
    "error": {
        "code": "sold_out",
        "message": "The requested product is sold out.",
        "shipments": []
    }
}
```

### Serializing Response Data

Similarly to success responses, error responses will be serialized using the specified error serializer in the configuration file. This defaults to the package's own `Flugg\Responder\Serializers\ErrorSerializer`, but can of course be changed by using the `serializer` method:

```php
return responder()->error()->serializer(ExampleErrorSerializer::class)->respond();
```

```php
return responder()->success()->serializer(new ExampleErrorSerializer())->respond();
```

You can create your own error serializer by implementing the `Flugg\Responder\Contracts\ErrorSerializer` contract.

## Handling Exceptions

No matter how much we try to avoid them, exceptions do happen. Responding to the exceptions in an elegant manner will improve the user experience of your API. The package can enhance your exception handler to automatically turn exceptions in to error responses. If you want to take use of this, you can either use the package's exception handler or include a trait as described in further details below.

#### Option 1: Replace `Handler` Class

To use the package's exception handler you need to replace the default import in `app/Exceptions/Handler.php`:

```php
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
```

With the package's handler class:

```php
use Flugg\Responder\Exceptions\Handler as ExceptionHandler;
```

***
This will not work with Lumen as its exception handler is incompatible with Laravel's. Look instead at the second option below.
***

#### Option 2: Use `ConvertsExceptions` Trait

The package's exception handler uses the `Flugg\Responder\Exceptions\ConvertsExceptions` trait to load of most of its work. Instead of replacing the exception handler, you can use the trait in your own handler class. To replicate the behavior of the exception handler, you would also have to add the following code to the `render` method:

```php
public function render($request, Exception $exception)
{
    $this->convertDefaultException($exception);

    if ($exception instanceof HttpException) {
        return $this->renderResponse($exception);
    }

    return parent::render($request, $exception);
}
```

If you only want to return JSON error responses on requests actually asking for JSON, you may wrap the code above in a `wantsJson` check as seen below:

```php
if ($request->wantsJson()) {
    $this->convertDefaultException($exception);

    if ($exception instanceof HttpException) {
        return $this->renderResponse($exception);
    }
}
```

### Converting Exceptions

Once you've implemented one of the above options, the package will convert some of Laravel's exceptions to an exception extending `Flugg\Responder\Exceptions\Http\HttpException`. It will then convert these to an error response. The table below shows which Laravel exceptions are converted and what they are converted to. All the exceptions on the right is under the `Flugg\Responder\Exceptions\Http` namespace and extends `Flugg\Responder\Exceptions\Http\HttpException`. All exceptions extending the `HttpException` class will be automatically converted to an error response. 

| Caught Exceptions                                               | Converted To                 |
| --------------------------------------------------------------- | ---------------------------- |
| `Illuminate\Auth\AuthenticationException`                       | `UnauthenticatedException`   |
| `Illuminate\Auth\Access\AuthorizationException`                 | `UnauthorizedException`      |
| `Symfony\Component\HttpKernel\Exception\NotFoundHttpException`  | `PageNotFoundException`      |
| `Illuminate\Database\Eloquent\ModelNotFoundException`           | `PageNotFoundException`      |
| `Illuminate\Database\Eloquent\RelationNotFoundException`        | `RelationNotFoundException`  |
| `Illuminate\Validation\ValidationException`                     | `ValidationFailedException`  |

You can disable the conversions of some of the exceptions above using the `$dontConvert` property:

```php
/**
 * A list of default exception types that should not be converted.
 *
 * @var array
 */
protected $dontConvert = [
    ModelNotFoundException::class,
];
```

***
If you're using the trait option, you can disable all the default conversions by removing the call to `convertDefaultException` in the `render` method.
***

#### Convert Custom Exceptions

In addition to letting the package convert Laravel exceptions, you can also convert your own exceptions using the `convert` method in the `render` method:

```php
$this->convert($exception, [
    InvalidValueException => PageNotFoundException,
]);
```

You can optionally give it a closure that throws the new exception, if you want to give it constructor parameters: 

```php
$this->convert($exception, [
    MaintenanceModeException => function ($exception) {
        throw new ServerDownException($exception->retryAfter);
    },
]);
```

### Creating HTTP Exceptions

An exception class is a convenient place to store information about an error. The package provides an abstract exception class `Flugg\Responder\Exceptions\Http\HttpException`, which has knowledge about status code, an error code and an error message. Continuing on our product example from above, we could create our own `HttpException` class:

```php
<?php

namespace App\Exceptions;

use Flugg\Responder\Exceptions\Http\HttpException;

class SoldOutException extends HttpException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $status = 400;

    /**
     * The error code.
     *
     * @var string|null
     */
    protected $errorCode = 'sold_out_error';

    /**
     * The error message.
     *
     * @var string|null
     */
    protected $message = 'The requested product is sold out.';
}
```

You can also add a `data` method returning additional error data:

```php
/**
 * Retrieve additional error data.
 *
 * @return array|null
 */
public function data()
{
    return [
        'shipments' => Shipment::all()
    ];
}
```

If you're letting the package handle exceptions, you can now throw the exception anywhere in your application and it will automatically be rendered to an error response.

```php
throw new SoldOutException();
```

# Contributing

Contributions are more than welcome and you're free to create a pull request on Github. You can run tests with the following command:

```shell
vendor/bin/phpunit
```

If you find bugs or have suggestions for improvements, feel free to submit an issue on Github. However, if it's a security related issue, please send an email to flugged@gmail.com instead.

# Donating

The package is completely free to use, however, a lot of time has been put into making it. If you want to show your appreciation by leaving a small donation, you can do so by clicking [here](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=PRMC9WLJY8E46&lc=NO&item_name=Laravel%20Responder&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted). Thanks!

# License

Laravel Responder is free software distributed under the terms of the MIT license. See [license.md](license.md) for more details.
