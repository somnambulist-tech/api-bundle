# Somnambulist API Bundle

[![GitHub Actions Build Status](https://img.shields.io/github/actions/workflow/status/somnambulist-tech/api-bundle/tests.yml?logo=github&branch=master)](https://github.com/somnambulist-tech/api-bundle/actions?query=workflow%3Atests)
[![Issues](https://img.shields.io/github/issues/somnambulist-tech/api-bundle?logo=github)](https://github.com/somnambulist-tech/api-bundle/issues)
[![License](https://img.shields.io/github/license/somnambulist-tech/api-bundle?logo=github)](https://github.com/somnambulist-tech/api-bundle/blob/master/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/somnambulist/api-bundle?logo=php&logoColor=white)](https://packagist.org/packages/somnambulist/api-bundle)
[![Current Version](https://img.shields.io/packagist/v/somnambulist/api-bundle?logo=packagist&logoColor=white)](https://packagist.org/packages/somnambulist/api-bundle)

Provides several helpers and support objects for better handling League Fractal with Symfony.
The integration with Fractal is based on Dingo API for Laravel: https://github.com/dingo/api

## Requirements

 * PHP 8.1+
 * somnambulist/fractal-bundle
 * symfony/twig-bundle (for documentation output)

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
          Assert\InvalidArgumentException: Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\AssertionExceptionConverter
          Assert\LazyAssertionException: Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\LazyAssertionExceptionConverter
          Doctrine\ORM\EntityNotFoundException: Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\EntityNotFoundConverter
          Doctrine\ORM\NoResultException: Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\EntityNotFoundConverter
          Somnambulist\Components\Models\Exceptions\EntityNotFoundException: Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\EntityNotFoundConverter
          Somnambulist\Components\ReadModels\Exceptions\EntityNotFoundException: Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\EntityNotFoundConverter
          Somnambulist\Components\ReadModels\Exceptions\NoResultsException: Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\EntityNotFoundConverter
          Symfony\Component\Messenger\Exception\HandlerFailedException: Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\HandlerFailedExceptionConverter
      api_root: '/api'
      doc_root: '/api/docs'
   subscribers:
      exception_to_json: true
      json_to_post: true
      request_id: true
   openapi:
      path: '%kernel.project_dir%/config/openapi'
      title: 'API Docs'
      version: '1.0.0'
      description: 'The documentation for the API'
      cache_time: 1400 # cache time in seconds for the generated docs
      tags:
          tag_name: "Description for the tag"
```

Extend the bundled [ApiController](docs/api_controller.md) and build your API.

Optionally you can:

 * [learn more about the API Controller](docs/api_controller.md)
 * [learn how to transform responses to JSON](docs/transforming_responses.md)
 * [learn how to document your API](docs/api_documentor.md)
 * [configure controller argument resolvers](docs/argument_resolvers.md)
 * [configure event subscribers](docs/event_subscribers.md)

### BC Breaks in V5

V5 drops all previously deprecated methods and makes changes to the signatures of the transformer types.
Transformer types are now readonly and cannot be modified once instantiated. This means that the constructor
args have changed to include all options and the order has been revised.

All response types default to a `data` key name, including `ObjectType` that previously was null. Now, you 
must specify a null key to remove it. Using a `data` key allows for applying metadata more easily to the
response object.

### BC Breaks in V4

V4 updates to league/fractal 0.20.0. This adds many type hints to properties and methods. Specifically
in `TransformerAbstract`, the various overridable arrays now require the `array` type hint.

In addition:

 * removed `RequestArgumentHelper` use FormRequests instead as they provide a cleaner, self-documenting API
 * removed the configuration for the arg helper from the bundle config
 * removed `withXXX` from `Response\AbstractType`, use `key`, `meta`, and `include` instead
 * the `AbstractType::include()` method now only accepts multiple string arguments
 * the request id header string is now set via a parameter (see docs)

### Package change in V3.5.0

From 3.5.0 `samj/fractal-bundle` has been replaced with `somnambulist/fractal-bundle` as samj has been
abandoned and archived. The replacement provides the same service resolution and allows transformers to
be tagged as well as auto-configured.

The only code change necessary is to replace `SamjFractalBundle` in bundles.php with `SomnambulistFractalBundle`.

### BC Breaks in V3

From v3.0.0 the library has been re-namespaced to `Somanmbulist\Bundles\ApiBundle`. Be sure to update
any references.

In addition:

 * `ReadModelTransformer` now requires somnambulist/read-models 2.0+
 * `UuidValueResolver` and `ExternalIdentityValueResolver` now require somnambulist/domain 4.0+

### BC Breaks in v2

From v2.0.0 the following changes have been made:

 * use PHP 7.4 features through-out the library
 * removed `Services` namespace component
 * `Converters` namespace was changed to `ExceptionConverters`
 * `Transformers` and `ExceptionConverters` are now part of the `Response` namespace
 * `TransformerBinding` has been removed in favour of `Types` with specific interfaces
 * `ApiController` methods `paginate`, `collection`, `item` are now strictly typed
 * `withIncludes` method accepts multiple string arguments instead of an array
 * all transformers should be registered as container services (transformer is now a string explicitly)

To switch from `TransformerBinding` replace each call to:

 * `TransformerBinding::item()` with `new ObjectType()`
 * `TransformerBinding::collection()` with `new CollectionType()` or `new IterableType()`
 * `TransformerBinding::paginate()` with `new PagerfantaType()` for Pagerfanta.
 
The constructor signatures are largely the same; except collection and pagerfanta have an
additional `key` as the last argument, defaulted to `data`.

When updating, remember to update the exception converters in your somnambulist.yaml config file
if using the included defaults.

### Tests

PHPUnit 9+ is used for testing. Run tests via `vendor/bin/phpunit`.

## Links

 * [Fractal Bundle](https://github.com/somnambulist-tech/fractal-bundle)
 * [Form Request Bundle](https://github.com/somnambulist-tech/form-request-bundle)
 * [The PHP League Fractal Docs](https://fractal.thephpleague.com/)
 * [Dingo API](https://github.com/dingo/api)
