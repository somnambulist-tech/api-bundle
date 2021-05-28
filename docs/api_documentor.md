## API Documentation (Experimental, v3.1+)

An OpenAPI documentation generator has been added. The docs are generated from the routes,
controllers and defined static configuration for responses. To make use of it you must have:

* symfony/twig-bundle
* somnambulist/form-request-bundle

__Note:__ the documentor currently supports a subset of the OpenAPI v3 standard and does not
allow or generate all possible features of the spec.

Next you must use form requests for all your controllers. The form requests should implement
the necessary constraints in a `rules()` method. The documentor will attempt to extract:

* required fields
* field types for date, datetime, float, array, uuid
* nested fields up to 1 level deep e.g.: this.*.that
* responses by response code

__Note:__ when using nested elements only `.*.` variants are supported and there must be an
`array` validation for the parent element:

```php
return [
    'name'        => 'required|min:8|max:100',
    'groups'      => 'array',
    'groups.*.id' => 'required|integer',
];
```

If the parent is not defined first, the documentor will generate an error.

Extraction is performed by using the primary routers route collection. No checks are made for
API routes as all routes are expected to be API endpoints. Each route must define a set of
responses in the "defaults" config key. The following keys are supported:

* `document`: true or false if the route should be included in the documentation (must be true to build docs)
* `responses`: An array of response codes the end point will return (required)
* `summary`: A short description describing all operations on this route (optional)
* `description`: A longer description for all operations on this route; can include CommonMark syntax (optional)
* `tags`: An array of tags to group the end point under e.g.: `['user']` (optional)
* `methods`: An array of properties for each method type

__Note:__ tags can be defined in the main package `openapi` configuration if you wish to add a short
description e.g.: `user: "Endpoints related to managing users."` etc. This is entirely optional
and is used by redoc to order the tags with descriptions first with a summary below the tag
heading.

The `methods` key should be indexed by the lowercase HTTP method name. It can accept the
following properties:

* `summary`: A short description for the resource (optional)
* `description`: A longer description for the resource, can include CommonMark syntax (optional)
* `operationId`: A unique operation id for this route and method e.g.: getListOfPets, postNewPet (optional)
* `deprecated`: true if the method has been deprecated, dont add if not needed (optional)

If no summary, description or operationId is given, then the route path will be used as the summary for the
operation.

__Note:__ if no responses are defined, the documentor will raise an error.

__Note:__ the `document` property can be set at the resource level so all API endpoints are
automatically included; then individual endpoints can be excluded by setting to `false`.
See the following example:

```yaml
# config/routes.yaml
apis:
    resource: 'routes/api.yaml'
    prefix: /api
    defaults:
        document: true
```

An example route using all available properties:

```yaml
api.v1.users.update_user:
    path: /users/{userId}
    controller: App\Users\Controllers\UpdateUserController
    methods: PUT|PATCH
    requirements:
        userId: '/\d+/'
    defaults:
        document: true
        summary: "Update the User"
        description: "Controls updates to the user object via either PUT or PATCH."
        tags: ['user']
        methods:
            put:
                description: "deprecated, use PATCH instead"
                operationId: putUpdateUserDetails
                deprecated: true
            patch:
                summary: 'Update specific User properties'
        responses:
            201: 'schemas/User'
            400: 'schemas/Error'
            422: 'schemas/Error'
```

### Example Data

New in 3.2.0: example data can be added for query arguments and request body form requests.
The form request needs to implement the `HasOpenApiExamples` interface and then return an
array of example data.

__Note:__ the two array formats are different and are handled slightly differently.

#### Query Examples

For query arguments, each field can have an example of the data it expects. For example:
a search endpoint may allow keyword searches or have special syntax. This can be set for
that field:

```php
return [
    'keywords' => 'string of keywords; use ~ for finding matches near the two words: this ~ that',
    'email'    => 'part_of@email',
];
```
Alternatively; if you would like to detail multiple examples of the fields usage, this can
be done by specifying an array of example => [summary, value] entries:

```php
return [
   'keywords' => [
       'example_1' => [
           'summary' => 'Single keyword matches',
           'value'   => 'this',
       ],
       'example_2' => [
           'summary' => 'Find matches where a word is near another word',
           'value'   => 'this ~ that',
       ],
   ],
];
```

#### Request Body Examples

For request body form requests i.e. POST / PUT requests; the format of the array responses
follows the OpenApi 3.0 definition. It is an array of examples that contains a summary and
then a value field that has all the fields defined. For example:

```php
return [
   'default' => [
       'summary' => 'A basic User with all required fields',
       'value'   => [
           'account_id' => '59b8ccbd-ac5d-436d-9f1b-02e9576faf47',
           'email'      => 'foo@bar',
           'password'   => 'bcrypt hashed string',
           'name'       => 'Foo Bar',
       ],
   ],
   'roles'   => [
       'summary' => 'A User with Roles that should be granted',
       'value'   => [
           'account_id' => '59b8ccbd-ac5d-436d-9f1b-02e9576faf47',
           'email'      => 'foo@bar',
           'password'   => 'bcrypt hashed string',
           'name'       => 'Foo Bar',
           'roles'      => [
               [
                   'id' => '9fc27d7c-22f7-43fe-9f1c-deafc971c95e',
               ],
           ],
       ],
   ],
];
```

### Component Templates

Responses can define no response (null / empty / ~), or the name of a template that is defined
in `config/openapi` e.g.: `schemas/User`. The template can be written in JSON or YAML. Multiple
folders can be used e.g.: `schemas`, `securitySchemas` etc. All templates in the `openapi` folder
will be automatically loaded and registered in the documentation using any folder as a component
scope.

__Note:__ don't mix JSON and YAML - pick one format and stick with it.

OpenApi 3.0 defines several scoped template types:

 * schemas - contains [schema objects](https://swagger.io/specification/#schema-object)
 * responses - contains [response objects](https://swagger.io/specification/#response-object)
 * parameters - contains [parameter objects](https://swagger.io/specification/#parameter-object)
 * examples - contains [example objects](https://swagger.io/specification/#example-object)
 * requestBodies - contains [request body objects](https://swagger.io/specification/#request-body-object)
 * headers - contains [header objects](https://swagger.io/specification/#header-object)
 * securitySchemes - contains [security objects](https://swagger.io/specification/#security-scheme-object)
 * links - contains [link objects](https://swagger.io/specification/#link-object)
 * callbacks - contains [callback objects](https://swagger.io/specification/#callback-object)

These can optionally be [reference objects](https://swagger.io/specification/#reference-object). If you
create folders with these names, then components with that key will be available for use within the
documentation. Note that route, query and request body discovery will not reference these so adding
further types beyond `schemas` may not be used - unless referenced in a response.

#### Defining a schema template

A User schema object can be defined in JSON as the following:

```json
{
    "type": "object",
    "properties": {
        "id": {
            "type": "string",
            "format": "uuid"
        },
        "email": {
            "type": "string"
        },
        "name": {
            "type": "string"
        },
        "password": {
            "type": "string",
            "format": "password"
        },
        "created_at": {
            "type": "string",
            "format": "date-time"
        },
        "updated_at": {
            "type": "string",
            "format": "date-time"
        }
    }
}
```

This can then be referenced in any response or other schema definition e.g. a result set definition.

__Note:__ templates may be nested by namespace, however these must be referred to using dot
separators. If you use `/` it will be converted to a dot automatically. The component schema spec
does not allow `/` in the component name.

Any valid schema can be used including nested objects if the endpoint returns nested data. You can
reference other templates by using the `"$ref": "$/components/<schema_name>/Object"`. For example:
you may add a pagination option that is used in all paginated responses:

```json
{
    "type": "object",
    "properties": {
        "data": {
            "type": "array",
            "items": {
                "$ref": "#/components/schemas/User"
            }
        },
        "meta": {
            "type": "object",
            "properties": {
                "pagination": {
                    "$ref": "#/components/schemas/Pagination"
                }
            }
        }
    }
}
```

The templates location can be changed by setting the `path` to a different value. In addition, you can
define the `title`, `version`, and `description` for the API documentation in the same config block.
Default templates for `Error` and `Pagination` are included in the `Resources/config` folder of this
bundle.

Finally: to display the docs, add to your `routes.yaml` file:

```yaml
api_doc:
   resource: "@SomnambulistApiBundle/Resources/config/routes.xml"
```

This will add the route `/docs` that will render the generated documentation. The generated docs are
cached in the default cache instance for 12 hours. This can be changed by setting the `cache_time`
value to something else. For example: you could add a config override in dev and set this to 1 to
regenerate on every request.

__Note:__ you can prefix, rename or re-implement the `ApiDocController` if you wish. The template can
be overridden by adding a `bundles/SomnambulistApi/` template override in your `templates` folder. See
the Symfony documentation for more on [overriding bundle templates](https://symfony.com/doc/current/bundles/override.html#templates).

If you override the controller you can then freely manipulate the collection of data to add more
before rendering the output.

Documentation is rendered using the standalone [redoc](https://redoc.ly/redoc) system.
