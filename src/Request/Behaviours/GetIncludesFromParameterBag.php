<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;

use function array_filter;
use function explode;

trait GetIncludesFromParameterBag
{
    protected function doGetIncludes(ParameterBag $bag): array
    {
        return array_filter(explode(',', $bag->get('include', '')));
    }
}
