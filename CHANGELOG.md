# 2.0.0 (2017-07-01)

### Breaking Changes

* Requires Fractal `^0.16.0` instead of `^.14.0` 
* The configuration file has been updated, remove existing `config/responder.php` file and run `php artisan vendor:publish --provider="Flugg\Responder\ResponderServiceProvider"` again
* Base transformer `Flugg\Responder\Transformer` moved to `Flugg\Responder\Transformers\Transformer`
* `Flugg\Responder\Traits` no longer exists, all traits have been moved to folders to better reflect the Laravel folder structure
* `Flugg\Responder\Traits\ConvertsParameter` has been removed, use the new `Flugg\Responder\Http\Middlewares\ConvertToSnakeCase` middleware instead
* `Flugg\Responder\Traits\HandlesApiErrors` has been moved to `Flugg\Responder\Exceptions\HandlesApiErrors`
* `Flugg\Responder\Traits\ThrowsApiErrors.php` has been moved to `Flugg\Responder\Http\Requests\ThrowsApiErrors.php`
* `Flugg\Responder\Traits\MakesApiRequests` has been moved to `Flugg\Responder\Testing\MakesApiRequests`
* `Flugg\Responder\Traits\RespondsWithJson` has been moved (and renamed) to `Flugg\Responder\Http\Controllers\MakesApiResponses`
* The `successResponse` method has been renamed to `success`
* The `errorResponse` method has been renamed to `error`
* You can no longer skip the data parameter for the `success` method, to allow the package to support primitives in the future
* `Flugg\Responder\Http\SuccessResponseBuilder` has been moved to `Flugg\Responder\Http\Responses\SuccessResponseBuilder`
* The `include` method of `Flugg\Responder\Http\Responses\SuccessResponseBuilder` has been renamed to `with`
* `getManager` and `getResource` removed from `Flugg\Responder\Http\Responses\SuccessResponseBuilder`
* Renamed dynamic relation methods in transformers from `relationName` to `includeRelationName`
* `Flugg\Responder\Serializers\ApiSerializer` has been renamed to `Flugg\Responder\Serializers\SuccessSerializer`
* The `transformer` method of the `Flugg\Responder\Contracts\Transformable` interface has been changed from static to non-static

### Features

* The package now has a changelog
* The documentation has been completely revamped
* Introduced a configuration option to set recursion limit
* Introduced configurable response decorators
* Allow transforming raw arrays and collections
* Allow sending resources into the `success` method
* Relations are now automatically eager loaded
* Allow filtering transformation data with a GET parameter automatically
* Added a shortcut `-m` to the `--model` modifier of the `make:transformer` command
* Added a new middleware to convert incoming request parameters to snake case: `Flugg\Responder\Http\Middlewares\ConvertToSnakeCase`
* Added a new transformer service to transform without serializing: `Flugg\Responder\Transformer`
* Added a new helper method to transform without serializing: `transform`
* Added a new facade to transform without serializing: `Flugg\Responder\Facades\Transformer`
* Added a new serializer to serialize without modifying the data: `Flugg\Responder\Serializers\NullSerializer`
* Added an error serializer: `Flugg\Responder\Serializers\ErrorSerializer`
* Added a new `$load` property to transformers to replicate Fractal's `$defaultIncludes`, including eager loading support
* Added a new `without` method to `Flugg\Responder\Http\Responses\SuccessResponseBuilder` to replicate Fractal's `parseExcludes` 
* Added a new `only` method to `Flugg\Responder\Http\Responses\SuccessResponseBuilder` to replicate Fractal's newly introduced `parseFieldsets` 
* Introduced a new dynamic method in transformers to filter relations: `filterRelationName`

### Bug Fixes

* Remove extra field added from deeply nested relations (fixes #33)
* Relations are not eager loaded when automatically including relations (fixes #48)

### Performance Improvements

* Add a new caching layer to the transformers, increasing performance with deeply nested relations
* The relation inclusion code has been drastically improved