## Event Subscribers

Several event subscribers are included and pre-configured to be enabled. These help with creating
responses, formatting errors, etc. They can be disabled in the main configuration by setting the
subscriber to `false`.

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

The following converters are provided:

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
use Somnambulist\Bundles\ApiBundle\Response\ExceptionConverterInterface;

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

__Note:__ the mapping is specific and does not check hierarchy; therefore if you extend an
exception you must explicitly map each one that needs converting. E.g. The Assertion library
requires 2 entries for the InvalidArgumentException and the LazyAssertionException.

The current `kernel.debug` setting is passed to the exception converter, and if enabled (not prod)
then the stack trace and any previous exceptions (if available) will be included in a `debug` key
in the response to help with debugging.

### Request ID Injector

The Request ID subscriber will check the headers of the incoming request for a specific header
and then capture that and make it available to Monolog via an auto-registered processor. In
addition the request id will be attached to the response from the API, ensuring the id is
propagated back / forward.

If no request id is found in the current request, a new UUIDv4 will be generated and assigned as
the request id.

For micro-services systems, this allows for a correlation id to be passed through the various systems
so that logs can be aggregated together to help with debugging and critical path diagnostics.

To use the processor with Monolog add a custom line formatter like the following:

```yaml
services:
    monolog.formatter.api_request:
        class: Monolog\Formatter\LineFormatter
        arguments:
            $format: "[%%extra.request_id%%] [%%datetime%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n"

``` 

And then in your `monolog.yaml` config file, add the formatter to the channels you want to use it on:

```yaml
monolog:
    handlers:
        main:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
            formatter: monolog.formatter.api_request

```

__Note:__ the request id is set to clear via `kernel.reset` and will not be available when `kernel.terminate`
is dispatched.
