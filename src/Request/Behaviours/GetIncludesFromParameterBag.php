<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;
use function array_filter;
use function explode;

/**
 * Trait GetIncludesFromParameterBag
 *
 * @package    Somnambulist\Bundles\ApiBundle\Request\Behaviours
 * @subpackage Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetIncludesFromParameterBag
 */
trait GetIncludesFromParameterBag
{

    protected function doGetIncludes(ParameterBag $bag): array
    {
        return array_filter(explode(',', $bag->get('include', '')));
    }
}
