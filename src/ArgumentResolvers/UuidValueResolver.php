<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\ArgumentResolvers;

use Ramsey\Uuid\Uuid as UuidFactory;
use Somnambulist\Domain\Entities\Types\Identity\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class UuidValueResolver
 *
 * @package    Somnambulist\Bundles\ApiBundle\ArgumentResolvers
 * @subpackage Somnambulist\Bundles\ApiBundle\ArgumentResolvers\UuidValueResolver
 */
class UuidValueResolver implements ArgumentValueResolverInterface
{

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return (
            null !== $request->get($argument->getName())
            &&
            Uuid::class === $argument->getType()
            &&
            UuidFactory::isValid($request->get($argument->getName()))
        );
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield new Uuid($request->get($argument->getName()));
    }
}
