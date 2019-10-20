# Somnambulist API Bundle

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
            Assert\LazyAssertionException: Somnambulist\ApiBundle\Services\Converters\AssertionExceptionConverter
    request_handler:
        per_page: 20
        max_per_page: 100
        limit: 100
    subscribers:
        exception_to_json: true
        json_to_post: true
```

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

 * page
 * per_page
 * limit
 
 `limit` is to fetch _only_ that many results and not a paginated set. `page` and `per_page` are
typically used together.

### JSON to POST arg converter

This subscriber will automatically decode a JSON payload that has been POST'd to an endpoint and
create a POST array of all the data. As this will override any other POST data, only expect one
form of data.

This is useful if you want to allow both form and JSON submissions e.g.: if a service will only
POST JSON instead of a more standard form request.

### Exception to JSON Converter

The exception subscriber converts exceptions to JSON responses with appropriate HTTP error codes.
Custom exceptions can be processed selectively via a class -> converter mapping. The converter
can be loaded as a service (must be if there are dependencies). The exception will then be
converted to an array of data with any additional context provided by the converter.

A generic converter and an Assertion specific one are included. When defining the converters as
services, be sure to tag them as `public` so that they are not removed from the container.

```yaml
services:
    App\Delivery\Api\Exceptions\Converters\:
        resource: '../src/Delivery/Api/Exceptions/Converters'
        public: true
```

You can add your own converters provided that they implement the interface and return an array
containing: `data` and `code` keys.

```php
<?php
use Somnambulist\ApiBundle\Services\Converters\ExceptionConverterInterface;

final class GenericConverter implements ExceptionConverterInterface
{
    public function convert(Exception $e): array
    {
        return [
            'data' => [
                'error' => $e->getMessage(),
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

### Tests

PHPUnit 8+ is used for testing. Run tests via `vendor/bin/phpunit`.

## Links

 * [The PHP League Fractal Docs](https://fractal.thephpleague.com/)
 * [SamJ Fractal Bundle](https://github.com/samjarrett/FractalBundle)
 * [Dingo API](https://github.com/dingo/api)
