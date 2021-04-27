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

* includes(Request $request) - returns an array of all requested objects to be included
* orderBy(Request $request) - returns an array of all requested fields to order results by
* page(Request $request, int $default = 1) - returns the current page from the request
* perPage(Request $request, int $default = null, int $max = null) - returns the number of results per page
* limit(Request $request, int $default = null, int $max = null) - returns the limit for the results
* offset(Request $request, int $limit = null) - returns the offset if not using pages
* nullOrValue(ParameterBag $request, array $fields, string $class = null, bool $subNull = false) - returns null or a value

### nullOrValue

`nullOrValue` allows pulling a value from a `ParameterBag` or null, while optionally returning
objects. For example: you may need multiple parameters in one go, or null. This can be achieved
by calling: `$this->nullOrValue($request->query, ['field1', 'field2'])`. This will return an
array of all the properties but only if they all exist.

If you need all fields even if they don't exist, set the fourth argument `subNull` to true, and
the result would be an array containing nulls for the missing keys.

Alternatively, if a class name is provided an instance of that class will be returned. Note
that when using this, the fields __must__ be defined in the order of the constructor arguments
on the class. This is very useful for casting parameters to value objects. If the class
constructor has `null`able arguments, set the fourth arg to `true` to allow nulls.

For example, get an array containing name, email, and phone - even if null:

```php
use Somnambulist\Bundles\ApiBundle\Request\RequestArgumentHelper;
use Symfony\Component\HttpFoundation\Request;

$helper = new RequestArgumentHelper();

$req = Request::create('/', 'GET', ['name' => 'bob', 'phone' => '12345678990'])->query;
$var = $helper->nullOrValue($req, ['name', 'email', 'phone'], null, true);
```

Get an object containing name, email, and phone:

```php
use Somnambulist\Bundles\ApiBundle\Request\RequestArgumentHelper;
use Symfony\Component\HttpFoundation\Request;

$helper = new RequestArgumentHelper();

$req = Request::create('/', 'GET', ['name' => 'bob', 'phone' => '12345678990'])->query;
$var = $helper->nullOrValue($req, ['name', 'email', 'phone'], Person::class);
```

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
