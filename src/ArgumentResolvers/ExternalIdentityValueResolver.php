<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\ArgumentResolvers;

use Somnambulist\Components\Models\Types\Identity\ExternalIdentity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ExternalIdentityValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $provider = $request->request->get('provider') ?? $request->query->get('provider');
        $identity = $request->request->get('identity') ?? $request->query->get('identity');

        if ($argument->isVariadic()
            || !is_string($provider)
            || !is_string($identity)
        ) {
            return [];
        }

        yield new ExternalIdentity($provider, $identity);
    }
}
