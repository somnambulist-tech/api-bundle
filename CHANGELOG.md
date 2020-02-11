Change Log
==========

2019-11-07 - 1.6.2
------------------

 * Fix missed type-hint that needs to be `Throwable` in ExceptionListener
 
2019-11-07 - 1.6.1
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
