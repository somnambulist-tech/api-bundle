## Controller Argument Resolvers

The following controller argument resolvers are included but _not_ enabled by default:

* UuidValueResolver
  Converts a UUID string into a som somnambulist/domain `AbstractIdentity` object. Type hint
  either `Uuid $id` or your value object that extends `AbstractIdentity` on a Controller to enable.
  Since v1.2.0, provided that the request contains a param with the same name as the type hint, it
  will resolve to a UUID. For example: the parameter is `$accountId` and your route is defined
  with `/account/{accountId}`, if the controller has a type-hint of: `Uuid $accountId` the UUID
  will be passed in.

* ExternalIdentityValueResolver
  Converts the parameters `provider` and `identity` to an ExternalIdentity object.
  Type hint `ExternalIdentity $id` on a controller to enable.

To enable argument resolvers add the following to your `services.yaml`:

```yaml
services:
    Somnambulist\Bundles\ApiBundle\ArgumentResolvers\UuidValueResolver:
        tags:
            - { name: controller.argument_value_resolver, priority: 105 }
```

or to load all resolvers:

```yaml
services:
    Somnambulist\Bundles\ApiBundle\ArgumentResolvers\:
        resource: '../../vendor/somnambulist/api-bundle/src/ArgumentResolvers/'
        tags:
            - { name: controller.argument_value_resolver, priority: 105 }
```

__Note:__ the priority needs to be set high enough that the resolvers are run before
the standard Symfony resolvers - specifically the default value resolver (priority 100).
See: https://symfony.com/doc/current/controller/argument_value_resolver.html for more
details on custom argument resolvers and priorities.
