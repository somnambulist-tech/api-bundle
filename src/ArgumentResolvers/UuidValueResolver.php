<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\ArgumentResolvers;

use Somnambulist\Components\Domain\Entities\Types\Identity\AbstractIdentity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use function is_a;

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
        return is_a($argument->getType(), AbstractIdentity::class, true);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $class = $argument->getType();

        yield new $class($request->get($argument->getName()));
    }
}
