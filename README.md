# Somnambulist API Bundle

[![GitHub Actions Build Status](https://github.com/somnambulist-tech/api-bundle/workflows/tests/badge.svg)](https://github.com/somnambulist-tech/api-bundle/actions?query=workflow%3Atests)

Provides several helpers and support objects for better handling League Fractal with Symfony.
The integration with Fractal is based on Dingo API for Laravel: https://github.com/dingo/api

## Requirements

 * PHP 7.2+
 * samj/fractal-bundle

## Installation

Install using composer, or checkout / pull the files from github.com.

 * composer require somnambulist/api-bundle

## Usage

Add the `SomnambulistApiBundle` to your `bundles.php` list and add a config file in `packages`
if you wish to configure the bundle. The following options can be set:

```yaml
somnambulist_api:
    exception_handler:
        converters:
            Assert\InvalidArgumentException: Somnambulist\ApiBundle\Services\Converters\AssertionExceptionConverter
            Assert\LazyAssertionException: Somnambulist\ApiBundle\Services\Converters\LazyAssertionExceptionConverter
    request_handler:
        per_page: 20
        max_per_page: 100
        limit: 100
    subscribers:
        exception_to_json: true
        json_to_post: true
```

### ApiController

An abstract `ApiController` can be inherited to provided a suitable base to work from. This extends
the Symfony controller and adds wrappers to the various helpers including the response factory and
argument helper. Additionally there are several method pass throughs and default response types.

The base methods are:

 * created() - return a 201 response with the specified binding
 * updated() - return a 200 response with the specified binding
 * deleted() - return a 204 response with a '{message: "..."}' payload
 * noContent() - returns a 204 with no content

The following pass through methods are available:

 * collection(TransformerBinding $binding) - return a JSON response for a collection of objects
 * item(TransformerBinding $binding) - return a JSON response for a single item
 * paginate(TransformerBinding $binding) - return a JSON response with a paginated result set 
 
 * includes(Request $request) - returns an array of all requested objects to be included
 * orderBy(Request $request) - returns an array of all requested fields to order results by
 * page(Request $request, int $default = 1) - returns the current page from the request
 * perPage(Request $request, int $default = null, int $max = null) - returns the number of results per page
 * limit(Request $request, int $default = null, int $max = null) - returns the limit for the results
 * offset(Request $request, int $limit = null) - returns the offset if not using pages
 * nullOrValue(ParameterBag $request, array $fields, string $class = null) - returns null or a value

### Transformer Bindings

A base ApiController is included that exposes internally Fractal and the various helpers of this
bundle. To use Fractal to transform an object to an array, create a `TransformerBinding`. There
are helper methods for: `collection`, `item` and `paginate`. This sets up the binding to be
passed to Fractal.

```php
<?php
use Somnambulist\ApiBundle\Services\Transformer\TransformerBinding;
use Somnambulist\ApiBundle\Tests\Support\Stubs\MyEntityTransformer;

class MyEntityController extends \Somnambulist\ApiBundle\Controllers\ApiController
{

    public function __invoke()
    {
        $entity  = new stdClass(); // fetch an entity from somewhere
        $binding = TransformerBinding::item($entity, MyEntityTransformer::class);

        return $this->item($binding);
    }
}
```

The binding encapsulates the resource, the transformer to apply (class name or instance, classes
will be resolved via the container, provided the transformers are public services) and assorted
other meta data and any includes to process.

To add includes or meta data call the `withXXX` method:

```php
<?php
use Somnambulist\ApiBundle\Services\Transformer\TransformerBinding;
use Somnambulist\ApiBundle\Tests\Support\Stubs\MyEntityTransformer;

TransformerBinding::item(new stdClass(), MyEntityTransformer::class)
    ->withIncludes(['child', 'child.child', '...'])
    ->withMeta(['array' => ['of' => 'meta data']])
;
```

`meta` data will be placed in an array key named `meta`. You should avoid exporting a similar key
at the root level of your transformer.

By default only collections will be exported under a specific key in the JSON response (defaults
to `data`). You can set this by using `withKey()` to use some other word. Note: this should be a
valid JSON object property.

For paginators the `withURL` should be used to set the current URL for that was requested. This
will then be used to build pagination links. In addition to the pagination meta data, various
X-API-Pagination headers are added along with a Link header for the next / previous results.

__Note:__ pagination is setup to work with PagerFanta and Doctrine ORM Paginator resources only.

The `ResponseFactory` can be accessed to generate an `array` instead of a `JsonResponse` object.
This allows that array to be further transformed, instead of having to JSON decode/encode from 
the response.

The transformer can be as simple or complex as you like. See the example in the tests or the
documentation for Fractal: https://fractal.thephpleague.com/transformers/ Just remember that
transformers should be configured as _public_ services so that they are available to SamJs
wrapper.

The serializer can be changed by either re-defining the `ResponseFactory` service or by calling
`setSerializer` before creating a response. This allows alternative encoding strategies to be
used e.g. JSON Data API.

### Request Handler

The request handler settings, allow changing the default values used in the RequestArgumentHelper.
These are used for limiting the maximum page size of paginated results, or setting a hard limit to
avoid an API endpoint returning too many results.

The defaults can be overridden at runtime by specifying the default / max as needed. The one
exception is `page`. This always returns `1` if not set or out of bounds.

The expected request vars are:

 * include
 * order
 * page
 * per_page
 * limit
 
 `limit` is to fetch _only_ that many results and not a paginated set. `page` and `per_page` are
typically used together.

`include` is for requesting data to be included in the response. It should be a comma separated
list of include options. These can then be passed to a view transformer / query command for
loading additional data. Typically this would only be used on view / GET type requests.

`order` is for specifying how the results should be ordered. It is a comma separated string of
valid field names. If a field is prefixed with a - (hyphen/minus sign) e.g. `-id` then the order
is set to `DESC`.

### JSON to POST arg converter

This subscriber will automatically decode a JSON payload that has been POST'd to an endpoint and
create a POST array of all the data. As this will override any other POST data, only expect one
form of data.

This is useful if you want to allow both form and JSON submissions e.g.: if a service will only
POST JSON instead of a more standard form request.

### Exception to JSON Converter

__Note:__ from `1.3.0` the error response format changed to:

 * message
 * errors
   * field_name/property_path -> error message
 * debug
   * message
   * class
   * trace
   * previous

The exception subscriber converts exceptions to JSON responses with appropriate HTTP error codes.
Custom exceptions can be processed selectively via a class -> converter mapping. The converter
can be loaded as a service (must be if there are dependencies). The exception will then be
converted to an array of data with any additional context provided by the converter.

As of `1.1.0` the mapping and conversion is handled by a dedicated class: `ExceptionConverter`. This
can be injected into other converters to convert wrapped exceptions using other converters.

The following converters are provided and auto-registered:

 * `GenericConverter` - fallback for converting any exception
 * `AssertionExceptionConverter` - extracts single failed property path from `Assert\InvalidArgumentException`
 * `LazyAssertionExceptionConverter` - extracts all failures from a `Assert\LazyAssertionException`
 * `HandlerFailedExceptionConverter` - extracts the first exception from a Messenger `HandlerFailedException`

You can tag services with: `somnambulist.api_bundle.exception_converter` and those will be pulled
into the `ServiceLocator` that is injected into the `ExceptionConverter`.

```yaml
services:
    App\Delivery\Api\Exceptions\Converters\:
        resource: '../src/Delivery/Api/Exceptions/Converters'
        tags: [somnambulist.api_bundle.exception_converter]
```

__Note:__ previously the services needed to be public, but using the tagged ServiceLocator, this is
no longer necessary.

__Note:__ you still have to map the exception to the converter in the `exception_handler` config
in the `somnambulist_api.exception_handler.converters` section. 

You can add your own converters provided that they implement the interface and return an array
containing: `data` and `code` keys.

```php
<?php
use Somnambulist\ApiBundle\Services\Converters\ExceptionConverterInterface;

final class GenericConverter implements ExceptionConverterInterface
{
    public function convert(Throwable $e): array
    {
        return [
            'data' => [
                'message' => $e->getMessage(),
            ],
            'code' => $e->getCode() && $e->getCode() >=400 && $e->getCode() < 500 ? $e->getCode() : 400
        ];
    }
}
```

`data` can contain any number of other elements but note that `debug` will be added if debugging
is enabled. `code` is the HTTP status code to send with the response e.g. 500 or 422 etc.

__Note:__ the code should be a valid and sensible HTTP error status code. For help determining an
appropriate code see: https://httpstatuses.com/ - specifically 400 / 500 error codes.

__Note:__ the mapping is specific and does not check hierarchy. Therefore if you extend an
exception you must explicitly map each one that needs converting. E.g. The Assertion library
requires 2 entries for the InvalidArgumentException and the LazyAssertionException. 

The current `kernel.debug` setting is passed to the exception converter, and if enabled (not prod)
then the stack trace and any previous exceptions (if available) will be included in a `debug` key
in the response to help with debugging.

### Controller Argument Resolvers

The following controller argument resolvers are included but _not_ enabled by default:

 * UuidValueResolver
   Converts a UUID string into a somnambulist/domain UUID object. Type hint `Uuid $id`
   on a Controller to enable. Since v 1.2.0 provided that the request contains a param
   with the same name as the type hint, it will resolve to a UUID. For example: the
   parameter is `$accountId` and your route is defined with `/account/{accountId}`, if
   the controller has a type-hint of: `Uuid $accountId` the UUID will be passed in.
   
 * ExternalIdentityValueResolver
   Converts the parameters `provider` and `identity` to an ExternalIdentity object.
   Type hint `ExternalIdentity $id` on a controller to enable.
   
To enable argument resolvers add the following to your `services.yaml`:

```yaml
services:
    Somnambulist\ApiBundle\ArgumentResolvers\UuidValueResolver:
        tags:
            - { name: controller.argument_value_resolver, priority: 105 }
```

or to load all resolvers:

```yaml
services:
    Somnambulist\ApiBundle\ArgumentResolvers\:
        resource: '../../vendor/somnambulist/api-bundle/src/ArgumentResolvers/'
        tags:
            - { name: controller.argument_value_resolver, priority: 105 }
```

__Note:__ the priority needs to be set high enough that the resolvers are run before
the standard Symfony resolvers - specifically the default value resolver (priority 100).
See: https://symfony.com/doc/current/controller/argument_value_resolver.html for more
details on custom argument resolvers and priorities.

### Tests

PHPUnit 8+ is used for testing. Run tests via `vendor/bin/phpunit`.

## Links

 * [The PHP League Fractal Docs](https://fractal.thephpleague.com/)
 * [SamJ Fractal Bundle](https://github.com/samjarrett/FractalBundle)
 * [Dingo API](https://github.com/dingo/api)
