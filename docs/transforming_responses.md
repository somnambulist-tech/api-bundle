## Transforming Responses

A base ApiController is included that exposes Fractal and the various helpers of this
bundle. To use Fractal to transform an object to an array, create an appropriate type
using either one of the provided types, or implement your own. The available types are:

* `ObjectType` - for single items
* `CollectionType` - specifically for Somnambulist/Collection
* `IterableType` - for other iterable collections of items
* `PagerfantaType` - specifically for Pagerfanta paginators

There are helper methods for: `collection`, `item` and `paginate` that are type-hinted
for specific types. The types act as a bridge to the Fractal resource types, allowing
meta-data, includes and other requirements to be passed through consistently.
Due to the use of specific types, the required arguments are enforced. To use other
types, directly access the converter: `->responseConverter()->toJson(<type>)` and pass
the type object for conversion to a JSON response.

```php
<?php
use Somnambulist\Bundles\ApiBundle\Response\Types\ObjectType;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\MyEntityTransformer;

class MyEntityController extends \Somnambulist\Bundles\ApiBundle\Controllers\ApiController
{
    public function __invoke()
    {
        $entity  = new stdClass(); // fetch an entity from somewhere
        $binding = new ObjectType($entity, MyEntityTransformer::class);

        return $this->item($binding);
    }
}
```

The type encapsulates the resource, the transformer to apply (class name or instance, classes
will be resolved via the container, provided the transformers are public services) and assorted
other meta data and any includes to process.

To add includes or meta-data call the appropriate method:

```php
<?php
use Somnambulist\Bundles\ApiBundle\Response\Types\ObjectType;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\MyEntityTransformer;

(new ObjectType(new stdClass(), MyEntityTransformer::class))
    ->include('child', 'child.child', '...')
    ->meta(['array' => ['of' => 'meta data']])
;
```

`meta` data will be placed in an array key named `meta`. You should avoid exporting a similar
key at the root level of your transformer.

By default, only collections will be exported under a specific key in the JSON response (defaults
to `data`). You can set this either at construction time, or by using `key()` to use some
other word. Note: this should be a valid JSON object property.

For paginators the URL must be specified when creating the binding. It may be changed using
`url` once the binding has been created. The provided URL will be used to generate the
pagination links. In addition to the pagination meta-data, various X-API-Pagination headers are
added along with a Link header for the next / previous results.

Includes can be passed parameters by adding a colon and then the options. This requires explicit
support in the transformer:

```text
?include=comments:limit(5|1):order(created_at|desc)
```

Then in the transformer:

```php
use League\Fractal\ParamBag;use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class MyEntityTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['comments'];
    private $validParams = ['limit', 'order'];
    
    public function transform(MyEntity $entity): array
    {
        return [];
    }
    
    public function includeComments(MyEntity $entity, ParamBag $params = null): Item
    {
        // do something with the params 
    }
}
```

From 3.8.0 support for specific fields on responses is added. This allows the requester to limit data
to specific elements. Fields should be requested as an array of the type and a comma separated list
of fields that should be included e.g.:

```text
?fields[my_entity]=id,name,type&fields[child]=name
```

This requires adding a named key to each element in the response e.g.:

```php
$type = new ObjectType(new stdClass(), MyEntityTransformer::class, key: 'my_entity');
```

and in transformers that include data:

```php
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class MyEntityTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['child'];
    
    public function transform(MyEntity $entity): array
    {
        return [];
    }
    
    public function includeChild(MyEntity $entity): Item
    {
        return $this->item($entity->child, MyEntityChildTransformer::class, 'child'); 
    }
}
```

The `ResponseConverter` can be accessed to generate an `array` instead of a `JsonResponse` object.
This allows that array to be further transformed, instead of having to JSON decode/encode from
the response.

The transformer can be as simple or complex as you like. See the example in the tests or the
[documentation for Fractal](https://fractal.thephpleague.com/transformers/). Since 3.5.0 transformers
must be registered with the container either by tagging with `somnambulist.fractal_bundle.transformer`
or automatically configured by extending `TransformerAbstract` and ensuring these are mapped in
the `services.yaml` file. See the bundle readme for examples.

Several default transformers are provided for very simple types:

* `ArrayTransformer` - previously called `PassThroughTransformer`, used for collections of arrays
* `StdClassTransformer` - casts stdClass objects to arrays
* `ReadModelTransformer` - if using the somnambulist/read-models library; calls toArray on the model

The serializer can be changed by either re-defining the `ResponseConverter` service or by calling
`setSerializer` before creating a response. This allows alternative encoding strategies to be
used e.g. JSON Data API.
