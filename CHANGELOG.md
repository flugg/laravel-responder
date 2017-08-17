# 2.0.7 (2017-08-17)

### Bug Fixes

* Fix explode default value for query string relations

# 2.0.6 (2017-08-16)

### Bug Fixes

* Explode query string relations on comma to support multiple relations

# 2.0.5 (2017-08-16)

### Features

* Automatic resolving of resource key if the data contains models

### Bug Fixes

* Add missing `LogicException` import to base `Transformer`
* Add missing `fields` key to error data of `ValidationFailedException`

# 2.0.4 (2017-08-15)

### Bug Fixes

* Change `Translator` contract with implementation to widen Laravel support

# 2.0.3 (2017-08-11)

### Bug Fixes

* Add missing `only` method to `SuccessResponseBuilder`

# 2.0.2 (2017-08-11)

### Bug Fixes

* Fix null data being converted to arrays for error responses

# 2.0.1 (2017-08-11)

### Bug Fixes

* Convert empty string messages in `HttpException` to null
* Remove `data` field from error response to make it behave as stated in the documentation

# 2.0.0 (2017-08-10)

Version `2.0.0` has been a complete rewrite of the package and brings a lot new stuff to the table, including this very new changelog. The documentation has also been revamped and explains all the new features in greater details. If you're upgrading from an earlier version, make sure to remove your `config/responder.php` file and rerun `php artisan vendor:publish --provider="Flugg\Responder\ResponderServiceProvider"` to publish the new configuration file. 

### Breaking Changes

* Fractal requirement changed to `0.16.0`  
* Moved `Flugg\Responder\Transformer` to `Flugg\Responder\Transformers\Transformer`
* Changed `Flugg\Responder\Traits\RespondsWithJson` to `Flugg\Responder\Http\Controllers\MakesResponses`
* Changed `Flugg\Responder\Traits\HandlesApiErrors` to `Flugg\Responder\Exceptions\ConvertsExceptions`
* Moved `Flugg\Responder\Traits\MakesApiRequests` to `Flugg\Responder\Testing\MakesApiRequests`
* Removed `Flugg\Responder\Traits\ConvertsParameter`, use new `ConvertToSnakeCase` middleware instead
* Removed `Flugg\Responder\Traits\ThrowsApiErrors`, manually override form requests to replicate
* Changed `Flugg\Responder\Exceptions\Http\ApiException` to `Flugg\Responder\Exceptions\Http\HttpException` 
* Renamed `$statusCode` property of the `HttpException` exceptions to `$status`
* Removed `Flugg\Responder\Exceptions\Http\ResourceNotFoundException`, handler now points to `PageNotFoundException`
* Renamed `Flugg\Responder\Serializers\ApiSerializer` to `Flugg\Responder\Serializers\SuccessSerializer`
* Renamed `successResponse` method of the `MakesResponses` trait to `success`
* Renamed `errorResponse` method of the `MakesResponses` trait to `error`
* Return `SuccessResponseBuilder` from `success` method instead of `JsonResponse`
* Return `ErrorResponseBuilder` from `error` method instead of `JsonResponse`
* Renamed `include` method to `with` on `SuccessResponseBuilder`
* Renamed `addMeta` method to `meta` on `SuccessResponseBuilder`
* Removed `transform` method on `SuccessResponseBuilder`, use `success` instead
* Removed `getManager` and `getResource` methods from `SuccessResponseBuilder`
* Changed `transformer` method of the `Transformable` interface to non-static
* Added an `include` prefix to include methods in transformers
* Renamed `transformException` of exception handler trait to `convertDefaultException`
* Renamed `renderApiError` of exception handler trait to `renderResponse`

### Features

* Added configurable response decorators
* Added a `recursion_limit` configuration option
* Allow transforming raw arrays and collections
* Allow sending transformers to the `success` method
* Allow sending resources as data to the `success` method
* Added a `only` method to `SuccessResponseBuilder` to replicate Fractal's `parseFieldsets`
* Added a `cursor` method to `SuccessResponseBuilder` for setting cursors
* Added a `paginator` method to `SuccessResponseBuilder` for setting paginators
* Added a `without` method to `SuccessResponseBuilder` to replicate Fractal's `parseExcludes` 
* Relationships are now automatically eager loaded
* Changed `with` method to allow eager loading closures
* Added a `filter_fields_parameter` configuration option for automatic data filtering
* Added a `PageNotFoundException` exception
* Added a `page_not_found` default error code
* Added a `ConvertToSnakeCase` middleware to convert request parameters to snake case
* Added a `Flugg\Responder\Transformer` service to transform without serializing
* Added a `Transformer` facade to transform without serializing
* Added a `transform` helper method to transform without serializing
* Added a `NullSerializer` serializer to serialize without modifying the data
* Added an `ErrorSerializer` contract for serializing errors
* Added a default `Flugg\Responder\Serializers\ErrorSerializer`
* Added a `$load` property to transformers to replicate Fractal's `$defaultIncludes` 
* Added a dynamic method in transformers to filter relations: `filterRelationName`
* Allow converting custom exceptions using the `convert` method of the `ConvertsExceptions` trait
* Added a shortcut `-m` to the `--model` modifier of the `make:transformer` command
* Added a `--plain` (and `-p`) option to `make:transformer` to make plain transformers
* Added possibility to bind transformers to models using the `TransformerResolver` class
* Added possibility to bind error messages to error codes using tne `ErrorMessageResolver` class
* Decoupled Fractal from the package by introducing a `TransformFactory` adapter
* Changed `success` to transform using an item resource if passed a has-one relation
* Added a `resource` method to the base `Transformer` for creating related resources

### Bug Fixes

* Remove extra field added from deeply nested relations (fixes #33)
* Relations are not eager loaded when automatically including relations (fixes #48)

### Performance Improvements

* Add a new caching layer to transformers, increasing performance with deeply nested relations
* The relation inclusion code has been drastically improved
