<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\ArgumentResolvers;

use Somnambulist\Domain\Entities\Types\Identity\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class UuidValueResolver
 *
 * @package Somnambulist\ApiBundle\ArgumentResolvers
 * @subpackage Somnambulist\ApiBundle\ArgumentResolvers\UuidValueResolver
 */
class UuidValueResolver implements ArgumentValueResolverInterface
{

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return null !== $request->get('id') && Uuid::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield new Uuid($request->get('id'));
    }
}
