<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\ArgumentResolvers;

use Somnambulist\Components\Models\Types\Identity\AbstractIdentity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use function is_string;
use function is_subclass_of;

class UuidValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->isVariadic()
            || !is_string($value = $request->attributes->get($argument->getName()))
            || null === ($class = $argument->getType())
            || !is_subclass_of($argument->getType(), AbstractIdentity::class, true)
        ) {
            return [];
        }

        yield new $class($value);
    }
}
