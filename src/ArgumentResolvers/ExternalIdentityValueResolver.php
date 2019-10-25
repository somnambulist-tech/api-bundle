<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\ArgumentResolvers;

use Somnambulist\Domain\Entities\Types\Identity\ExternalIdentity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class ExternalIdentityValueResolver
 *
 * @package Somnambulist\ApiBundle\ArgumentResolvers
 * @subpackage Somnambulist\ApiBundle\ArgumentResolvers\ExternalIdentityValueResolver
 */
class ExternalIdentityValueResolver implements ArgumentValueResolverInterface
{

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return
            null !== $request->get('provider')
            &&
            null !== $request->get('identity')
            &&
            ExternalIdentity::class === $argument->getType()
        ;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield new ExternalIdentity($request->get('provider'), $request->get('identity'));
    }
}
