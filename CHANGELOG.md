Change Log
==========

2024-06-12 - 6.0.1
------------------

 * Add check for uninitialised configuration values that can be triggered during composer require

2024-02-24 - 6.0.0
------------------

 * Raise minimum Symfony to 6.4
 * Add support for Symfony 7
 * Update redoc version to 2.1.3
 * Replace eloquent/enumeration with somnambulist/enumeration

2023-07-07
----------

 * Raise minimum Symfony version to 6.2
 * Remove deprecated interfaces
 * Update tests to remove deprecated method calls

2023-01-29 - 5.2.3
------------------

 * Fix bug in API Expression to DBAL helper not using correct operator mapping when using comparison

2023-01-29 - 5.2.2
------------------

 * Fix default key name should be consistent between new and fromFormRequest (ObjectType)

2023-01-29 - 5.2.1
------------------

 * Fix key name can be null in response types

2023-01-28 - 5.2.0
------------------

 * Add compound `Searchable` interface and apply to Search and FormRequest
 * Add support for ILIKE / NOT ILIKE
 * Add support to DBAL helper for ILIKE via `comparison` method call
 * Fix DBAL helper to work from `Searchable` form request
 * Fix Filter decoders to work with `Searchable` instead of deprecated `FormRequest`

2023-01-26 - 5.1.0
------------------

 * Add various interfaces for form requests
 * Add `ViewFormRequest` as a base for view requests that only deal with includes/fields
 * Add new traits for the shared validated data methods in view and search requests
 * Revise response types to allow the base FormRequest and check for interfaces
 * Deprecate the `FormRequest` in favour of the new versions / base instance

2023-01-23
----------

 * Add `SearchFormRequest` that only uses validated data for search parameters

2023-01-23 - 5.0.1
------------------

 * Add query interfaces to `AbstractExpressionQuery` from updated domain library

2023-01-22 - 5.0.0
------------------

 * Revise `CompositeExpression` to drop `get` from methods for consistency
 * Add `AbstractExpressionQuery` for use with `QueryBus`

2023-01-20
----------

 * Add API expression to DBAL service for converting from API filters to DBAL query
 * Fix decoding multiple filters on the same field for OpenStack filters

2023-01-19
----------

 * Add support for API query filters in form requests
 * Add support for API query offset markers in form requests
 * Add query filter parsing for common API specs as provided by somnambulist/api-client

2022-10-17
----------

 * Add `EntityNotFoundConverter` for handling exceptions from read-models and Doctrine

2022-09-26
----------

 * Require PHP 8.1+

2022-09-07
----------

 * Change response type constructors and factory methods to include all possible arguments
 * Change response type objects to value-objects by removing setters

2022-09-06
----------

 * Add check for docs / api routes to avoid using exception response converter on non-API routes
 * Add yaml response examples in resources
 * Add support for URL matching on exception handler to allow using with non-API projects
 * Remove missed unnecessary docblock comments
 * Remove reference to removed interface in OpenApiGenerator

2022-08-08 - 4.3.2
------------------

 * Fix `FormRequest` added previous fix erroneously to order.

2022-08-08 - 4.3.1
------------------

 * Fix `FormRequest` requires int or null, but get() will return string

2022-08-02 - 4.3.0
------------------

 * Add `::fromFormRequest()` to response type objects for easier creation
 * Fix api_controller.md documentation referring to removed functionality
 * Fix `FormRequest::doGetFields()` returns array so requires `ParameterBag::all()` not `get`
 * Fix `FormRequest::offset()` not checking for an offset value first
 * Fix `FormRequest::orderBy()` not using validated data default value if present
 * Fix `FormRequest::perPage()` not using validated data default value if present
 * Fix `FormRequest::limit()` not using validated data default value if present

2022-07-26 - 4.2.0
------------------

 * Add support for monolog 3.0
 * Remove unnecessary docblock comments
 * Bump redoc version to rc 72

2022-05-23 - 4.1.1
------------------

 * Fix min/max to cast all other values to float if ctype_digit test fails (ranges should only be numbers) 

2022-05-23 - 4.1.0
------------------

 * Add additional rule converters for rules added in validation lib: sometimes, any_of, rejected
 * Add support for generating model docs in the menu, requires tags are used on models
 * Fix bug in `DefaultConverter` where pipe delimited values may sometimes be present
 * Fix bug in rule handling where pipe delimited strings are not recoded to `~~` causing encode issues later

2022-05-09 - 4.0.1
------------------

 * Fix compatibility with the latest fractal version

2022-03-21 - 4.0.0
------------------

 * Update Fractal to 0.20.0 (via fractal-bundle)
   This is a BC breaking update as fractal adds method argument and return type hints
 * Remove all previously deprecated methods, classes, and configuration

2022-03-21 - 3.8.0
------------------

 * Add support for fields in response transformations; fields allows returning only partial data from a response
 * Add documentation for fields and include parameters
 * Deprecate `withKey`, `withIncludes`, `withMeta` on `Response\Types\AbstractType`; use `key`, `include`, and `meta`
 * Deprecate `RequestArgumentHelper`; use form requests instead

2022-03-01 - 3.7.2
------------------

 * Allow later versions of psr/log

2021-12-14 - 3.7.1
------------------

 * Add missed return types added in SF 6

2021-12-14 - 3.7.0
------------------

 * Add support for Symfony 6.0
 * Add support for `form-request-bundle` 2.0

2021-10-05 - 3.6.0
------------------

 * Deprecate passing array as first arg on `withIncludes` on `AbstractType`
 * Add `FormRequest` with support for includes, page etc.
 * Add `form-request-bundle` as a dependency
 * Bump minimum Symfony version to 5.3

2021-09-30 - 3.5.0
------------------

 * Switch to `somnambulist/fractal-bundle` as replacement for the deprecated `samj/fractal-bundle`

2021-09-09 - 3.4.4
------------------

 * Fix array query arguments were not being handled correctly in Api documentor
 * Partially refactor the schema building into separate helper objects
 
2021-09-09 - 3.4.3
------------------

 * Fix security would add an array of null instead of remove the empty key

2021-08-05 - 3.4.2
------------------

 * Improvements to `OpenApiGenerator` enum handling, with thanks to [Jason Hofer](https://github.com/jasonhofer)

2021-08-03 - 3.4.1
------------------

 * Fix `RuleConverters` not checking for the rule converter before attempting to apply it

2021-07-27 - 3.4.0
------------------

 * Add `OpenApiExamples` as an attribute
 * Add support for OpenAPI Auth definitions
 * Deprecated the `HasOpenApiExamples` interface
 * Refactored the `OpenApiGenerator` rule processes to external classes
 
2021-06-07 - 3.3.7
------------------

 * Enum as array values, with thanks to [Jason Hofer](https://github.com/jasonhofer)

2021-06-05 - 3.3.6
------------------

 * Improvements to `OpenApiGenerator`, with thanks to [Jason Hofer](https://github.com/jasonhofer)

2021-05-28 - 3.3.5
------------------

 * Add tag definitions to the main openapi configuration block in the package settings

2021-05-27 - 3.3.4
------------------

 * Fix incorrect usage of summary/description when building path and operation definitions
   * summary/description are kept but are now assigned to the path object
   * a separate `methods` key has been added to define summary/description/operationId for each method
   * `deprecated` has been added to the operation
   * `operation` is now `operationId`
   * if there is no summary/description/operationId the summary is set to the route path
 * Fix cache item was missing the save call

2021-05-26 - 3.3.3
------------------

 * Fix additional bugs in `OpenApiGenerator`:
   * nested schemas are not being aliased correctly
   * schemas not in "schemas" folder were not being imported correctly
   * nested schema files cannot use a `/` - only a-z, - _ and . are allowed
   * required fields in nested objects that have no required fields should not be set
 * Fix incorrect documentation in `HasOpenApiExamples`, it is value (singular)

2021-05-26 - 3.3.2
------------------

 * Fix bug in `OpenApiGenerator` array union operator does not merge all values

2021-05-12 - 3.3.1
------------------

 * Fix in `ApiController` where route parameters are not passed to URL generation

2021-04-28 - 3.3.0
------------------

 * Add helper to generate absolute URLs from the current request/form request object
 * Add trait for adding bus helpers (if using `somnambulist/domain`) on `ApiController`

2021-04-27 - 3.2.0
------------------

 * Add support for examples through the `HasOpenApiExamples` interface for FormRequests
 * Add description to route defaults meta-data for long descriptions
 * Split up the docs into smaller individual files

2021-04-23 - 3.1.0
------------------

 * Add default values to `GetPaginationFromParameterBag` trait for easier implementation

2021-04-16
----------

 * Add `ServiceList` as a default schema example
 * Fix bug handling form request rules: they can be arrays of rules
 * Fix bug where `MutableCollection` is being used, but it is not a dependency
 * Refactor `RequestArgumentHelper` to use traits so functionality can be shared
 * Make trait properties and methods protected instead of private

2021-04-14
----------

 * Add initial API documentation generation by using conventions / config
 * Add example default schema definitions for Error and Pagination

2021-03-23 - 3.0.2
------------------

 * Fix bug in `nullOrValue` would not return null in array of fields with `subNull` true

2021-03-18
----------

 * Update `UuidValueResolver` to allow resolving any type of `AbstractIdentity` value object

2021-03-17 - 3.0.1
------------------

 * Add ability to allow null values for multiple fields in `RequestArgumentHelper::nullOrValue`

2021-02-05
----------

 * Add priority to event subscribers to avoid collisions with `somnambulist/form-request-bundle`

2021-01-21 - 3.0.0
------------------

 * Require PHP 8
 * Update to domain 4.0 and read-models 3.0 

2021-01-18
----------

 * Update tests after namespacing
 * Fix PHP8 compatibilities

Note: this version was originally 3.0.0; however it was tagged prematurely and 3.0.0
has been re-released as a PHP 8+ library.

2020-08-29
----------

 * Re-namespace to `Somnambulist\Bundles`

2020-08-26 - 2.0.0
------------------

 * Require PHP 7.4
 * Require Symfony 5+
 * Major refactoring of the internals with many BC breaks
 * Add extra transformers 

2020-08-21 - 1.7.5
------------------

 * Fix using deprecated `JsonResponse::create()` method

2020-05-22 - 1.7.4
------------------

 * Fix bug where all collections are treated as a paginator in `ResponseFactory`
 * Made URL required on paginator bindings

2020-04-08 - 1.7.3
------------------

 * Add default option to `RequestArgumentHelper::orderBy`

2020-03-31 - 1.7.2
------------------

 * Fix rare instance when request_id has not been set in injector subscriber

2020-02-20 - 1.7.1
------------------

 * Fix only set `request_id` in processor extra data if there actually is one

2020-02-18 - 1.7.0
------------------

 * Added `RequestIdInjectorSubscriber` to automatically handle setting request ids

2020-02-11 - 1.6.2
------------------

 * Fix missed type-hint that needs to be `Throwable` in ExceptionListener
 
2020-02-05 - 1.6.1
------------------

 * Update samj/fractal-bundle to 3.X branch for SF 5

2019-11-07 - 1.6.0
------------------

 * Added Symfony 5 support

2019-11-07 - 1.5.0
------------------

 * Added `orderBy` to RequestArgumentHelper for processing order by request arg.

2019-11-07 - 1.4.0
------------------

 * Added `nullOrValue` helper to fetch multiple values from the request fields
   The method works on the `ParameterBag` from the request so can work on GET or POST data.
 * Changed `ExceptionConverterInterface` to use `Throwable` to be able to handle `ErrorExceptions`

2019-11-07 - 1.3.0
------------------

 * Refactored `AssertionExceptionConverter` into 2 separate converters
 * Added `LazyAssertionExceptionConverter`
 * Changed output of errors to match FormRequest message/errors field array

2019-11-07 - 1.2.0
------------------

 * Expanded `UuidValueResolver` to work on any named UUID property

2019-11-07 - 1.1.0
------------------

 * Added messenger exception converter
 * Added tag support for register exception converters (`somnambulist.api_bundle.exception_converter`)
 * Added exception class name to debug output
 * Refactored exception conversion to a separate service

2019-11-04 - 1.0.3
------------------

 * Fixed bug in `ResponseFactory` not correctly initialising parsed URL array

2019-10-25 - 1.0.2
------------------

 * Added controller argument resolvers for UUID and `ExternalIdentity`
 * Added `includes()` to `ApiController` and `RequestArgumentHelper` service

2019-10-22 - 1.0.1
------------------

 * Fixed bug with alias of `perPage` (was per_page)

2019-10-18 - 1.0.0
------------------

Initial commit.
