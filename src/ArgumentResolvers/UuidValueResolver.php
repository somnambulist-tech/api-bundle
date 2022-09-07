<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\ArgumentResolvers;

use Somnambulist\Components\Models\Types\Identity\AbstractIdentity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

use function is_a;

class UuidValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_a($argument->getType(), AbstractIdentity::class, true);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $class = $argument->getType();

        yield new $class($request->get($argument->getName()));
    }
}
