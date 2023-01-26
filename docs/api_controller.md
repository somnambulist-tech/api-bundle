## ApiController

An abstract `ApiController` can be inherited to provide a suitable base to work from.
This extends the Symfony controller and adds wrappers to the various helpers including
the response factory and argument helper.

The base methods are:

* created() - return a 201 response with the specified binding
* updated() - return a 200 response with the specified binding
* deleted() - return a 204 response with a '{message: "..."}' payload
* noContent() - returns a 204 with no content

The following pass through methods are available:

* collection(CollectionType $type) - return a JSON response for a collection of objects
* item(ObjectType $type) - return a JSON response for a single item
* paginate(PagerfantaType $type) - return a JSON response with a paginated result set

### Requests

`FormRequest` objects should be used for all API controller actions - especially if you want automatic
API documentation generating.

`api-bundle` includes several custom FormRequests to help with some common tasks:

 * SearchFormRequest - for searches
 * ViewFormRequest - adds only includes / fields support
 * FormRequest (deprecated from 5.1.0)

__Note:__ Search and View FormRequests pull data only from the validated data bag and do not access the
request query object. If validation is not performed, these will be empty or only contain the defaults.

Search and API FormRequest include:

 * includes (`include` is the argument)
 * filters (or filter, the search criteria to apply on the request)
 * fields (`fields` is the argument as: `fields[object_name]=field,field2`)
 * page
 * per_page
 * marker (used as offset value for pagination in OpenStack implementations)
 * offset (will use `offset` if provided and fall back to `page` otherwise)
 * limit
 * order (comma separated list using `-` to indicate `DESC`, also supports `field:asc|desc`)

When using these query arguments, you should add appropriate rules to the validation process to ensure that
the data received is within the expected ranges. For example: page should never be less than 1, and order
should only contain valid ordering values. For example:

```php
use Somnambulist\Bundles\ApiBundle\Request\SearchFormRequest;

class MyFormRequest extends SearchFormRequest
{
    public function rules() : array
    {
        return [
            'include'  => 'sometimes|any_of:object1,object2,object3.child',
            'order'    => 'sometimes|default:-updated_at|any_of:name,-name,created_at,-created_at,updated_at,-updated_at',
            'page'     => 'integer|default:1',
            'per_page' => 'integer|default:30|max:500',
        ];
    }
}
```

Or for pagination the helper method can be used:

```php
use Somnambulist\Bundles\ApiBundle\Request\SearchFormRequest;

class MyFormRequest extends SearchFormRequest
{
    public function rules() : array
    {
        return array_merge(
            $this->paginationRules(),
            [
                'include'  => 'sometimes|any_of:object1,object2,object3.child',
                'order'    => 'sometimes|default:-updated_at|any_of:name,-name,created_at,-created_at,updated_at,-updated_at',
            ]
        );
    }
}
```

The helper methods returns all rules for paging through results, configured from the current requests values
for per page, max page size and limit.

Using form requests in this way ensures that your API docs (if using the documentor) are always up-to-date
as the form request will be introspected to build the request / query body for the API docs.

Form requests can be further enhanced by using the validated data to return a data transfer object from the
request instance that already contains the data necessary. For example: when POST'ing data to create a new
record, you could return a pre-built CreateXXX command for dispatching, or a query object that is ready
for executing.

### Request Filter Support

New in 5.0 is support in FormRequest for `filters`. Filters are search criteria to and several formats are
natively supported, however additional types can be added. Filters are defined either on the `filter` or
`filters` query argument. This is an associative array of field => value pairs, that depending on decoder,
can be multiple values per field.

The following filter decoders are included in this library:

 * SimpleApi
 * JSON API
 * OpenStack API
 * Nested Array
 * Compound Nested Array

These correspond to the API query encoders found in [somnambulist/api-client](https://github.com/somnambulist-tech/api-client)
package. Nested and Compound Nested are custom extended criteria types that allow fine-grained control of
criteria including things like `is null` and `is not null` along with `in`, `and` and `or` compound statements.

JSON API approximates the JSON API filtering standard and supports all common operations defined within. Custom
extensions are not directly supported, but array values for `IN` queries will work.

OpenStack follows the OpenStack API filtering standard. This allows for nested `AND` and multiple comparisons
against the same field using `eq|neq|gte|gt|lt|lte|like`. api-client extends this to include `nin` (not in)
and `nlike` (not like).

SimpleApi is the most basic and allows for single field -> value pairs, and will decode comma separated strings
to array values for `IN` queries. SimpleApi additionally allows the filter query key to be specified if one is
used (JSON API, Nested, and Compound Nested specify the key, OpenStack does not use one at all).

When using request filters, they should be included in the validation rules to ensure that only valid values
are passed into the decoder. The decoders only check structure and not if the payload is potentially malicious.

As a further helper: an ApiExpression to DBAL QueryBuilder service is provided. This will take the APIExpression
and convert it to a DBAL query builder instance. This requires providing a mapping of API query field to table
column. All data parameters are set as named parameters and the various SQL functions are called from DBAL
ExpressionBuilder.

### Domain Helpers

If you are using [somnambulist/domain](https://github.com/somnambulist-tech/domain) library, there is
now a trait to add the query, command, job, and event buses to the list of controller services. To use
it extend the `ApiController` and use the `AddDomainServicesHelpers` to your traits. This will add
helpers to access `query()`, `command()`, `job()`, and `event()` in the controller.

For example:

```php
use Somnambulist\Bundles\ApiBundle\Controllers\ApiController;
use Somnambulist\Bundles\ApiBundle\Controllers\Behaviours\AddDomainServicesHelpers;

class MyApiController extends ApiController
{
    use AddDomainServicesHelpers;
    
    public function __invoke()
    {
        $result = $this->query()->execute(new SomeQueryObject());
        
        $this->job()->queue(new SomeJob());
    }
}
```
