<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;

use function array_filter;

trait GetFiltersFromParameterBag
{
    protected function doGetFilters(ParameterBag $bag): array
    {
        if ($bag->has('filter')) {
            return array_filter($bag->all('filter'));
        }

        return array_filter($bag->all('filters'));
    }
}
