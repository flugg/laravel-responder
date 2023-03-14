# 3.3.0 (2023-03-14)

### Features

* Add Laravel 10.0 support
* Add Fractal 0.20 support

# 3.2.0 (2022-03-25)

### Features

* Add Laravel 9.0 support

# 3.1.3 (2021-01-07)

### Features

* Add PHP 8.0 support

# 3.1.2 (2020-09-10)

### Features

* Add Laravel 8.0 support

# 3.1.1 (2020-03-18)

### Bug Fixes

* Remove typehint from exception handler

# 3.1.0 (2020-03-09)

### Features

* Add Laravel 7.0 support

# 3.0.6 (2019-08-28)

### Features

* Add Laravel 6.0 support

# 3.0.5 (2019-03-01)

### Features

* Add Laravel 5.8 support

# 3.0.4 (2018-09-05)

### Bug Fixes

* Add missing header call in `ConvertsExceptions`

# 3.0.3 (2018-09-05)

### Features

* Add Laravel 5.7 support

# 3.0.2 (2018-02-06)

### Bug Fixes

* Change Collection's `intersectKey` with `array_key_intersect` in transformer as the method isn't available in all Laravel versions

# 3.0.1 (2018-02-06)

### Features

* When requesting non-whitelisted nested relations, it now returns any relation up to the one that's not whitelisted
* It will now automatically camel case relations before loading them from the model allowing for snake cased relations in transformers

### Bug Fixes

* Fix bug concerning circular relationship mappings
* It will now correctly look for default relations in requested relations that are nested

# 3.0.0 (2018-01-28)

Version `3.0.0` contains many bug fixes, but also quite a lot of new features and changes. The entire relationship logic has been rewritten to improve performance, security and stability among other improvements. There has also been big focus on improving test coverage for this release and we're now right below 90% coverage.

### Breaking Changes

* Fractal requirement changed to `0.17.0`
* Whitelisted relationships now requires a transformer mapping in order for eager loading to take effect
* Relationships will now only be eager loaded if you have specified a transformer with whitelisted relationships
* The `transform` method of `Transformer` service and `Transformer` facade has been renamed to `make` and now returns a `TransformBuilder`
* The `Flugg\Responder\Transformer` service has been renamed to `Transformation`
* The `transform` helper function has been renamed to `transformation`
* The `Transformer` facade has been renamed to `Transformation`
* `NullSerializer` has been renamed to `NoopSerializer`

### Features

* New integration test suite
* Added support for primitive resources when including relations
* Added a `fallback_transformer` configuration option to change the fallback transformer
* Added a `error_message_files` configuration option to change translation files to load error messages from
* Support for specifying query constraints for relationships as "load" methods in transformers
* You no longer need to use the `resource` method inside "include" methods in transformers, you can just return the data directly

### Bug Fixes

* It will now only eager load relationships that are whitelisted
* You can now call multiple transformers in sequence without problems
* Associative arrays will now be treated as an item rather than a collection
* A default resource key of `data` is now set, allowing to use the `only` method even when data is empty

# 2.0.14 (2018-01-23)

### Features

* Added support for Laravel 5.6

### Bug Fixes

* Removed extra end bracket in `PrettyPrintDecorator`

# 2.0.13 (2018-01-23)

### Features

* Added support for PHP 7.2
* Added two new optional decorators: `PrettyPrintDecorator` and `EscapeHtmlDecorator`

### Security Fixes

* New transformers now has an empty array as whitelisted relations instead of a wildcard

### Bug Fixes

* Parameters are stripped away from relations before eager loading
* Changed `TransformFactory` from singleton to a normal binding
* `NullSerializer` now returns `null` instead of an empty array on null resources
* Relations that have an "include" method in a transformer is no longer eager loaded

# 2.0.12 (2017-10-17)

### Bug Fixes

* Remove `string` typehint for `$errorCode`

# 2.0.11 (2017-09-23)

### Bug Fixes

* Change `Responder` and `Transformer` from singletons to regular bindings

# 2.0.10 (2017-09-17)

### Bug Fixes

* Rebind incompatible translator implementation with Lumen

# 2.0.9 (2017-09-02)

### Bug Fixes

* Add JSON check to exception handler 

# 2.0.8 (2017-08-17)

### Bug Fixes

* Fix a query string relation parsing bug

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
* Added possibility to bind error messages to error codes using the `ErrorMessageResolver` class
* Decoupled Fractal from the package by introducing a `TransformFactory` adapter
* Changed `success` to transform using an item resource if passed a has-one relation
* Added a `resource` method to the base `Transformer` for creating related resources

### Bug Fixes

* Remove extra field added from deeply nested relations (fixes #33)
* Relations are not eager loaded when automatically including relations (fixes #48)

### Performance Improvements

* Add a new caching layer to transformers, increasing performance with deeply nested relations
* The relation inclusion code has been drastically improved
