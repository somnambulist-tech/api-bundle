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

From V4.0 `FormRequest` objects should be used for all API controller actions, except `DELETE` or `HEAD`
that should not have a request body.

`api-bundle` includes an extension adding dedicated methods for extracting common API data from the request:

 * includes (`include` is the argument)
 * fields (`field` is the argument as: `field[object_name]=field,field2`)
 * page
 * per_page
 * offset (will use `offset` if provided and fall back to `page` otherwise)
 * limit
 * order (comma separated list using `-` to indicate `DESC`)

When using these query arguments, you should add appropriate rules to the validation process to ensure that
the data received is within the expected ranges. For example: page should never be less than 1, and order
should only contain valid ordering values. For example:

```php
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;

class MyFormRequest extends FormRequest
{
    public function rules() : array
    {
        return [
            'include'  => 'sometimes|in:object1,object2,object3.child',
            'order'    => 'sometimes|default:-updated_at|any_of:name,-name,created_at,-created_at,updated_at,-updated_at',
            'page'     => 'integer|default:1',
            'per_page' => 'integer|default:30|max:500',
        ];
    }
}
```

Using form requests in this way ensures that your API docs (if using the documentor) are always up-to-date
as the form request will be introspected to build the request / query body for the API docs.

Form requests can be further enhanced by using the validated data to return a data transfer object from the
request instance that already contains the data necessary. For example: when POST'ing data to create a new
record, you could return a pre-built CreateXXX command for dispatching, or a query object that is ready
for executing.

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
