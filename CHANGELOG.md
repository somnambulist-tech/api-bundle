Change Log
==========

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
