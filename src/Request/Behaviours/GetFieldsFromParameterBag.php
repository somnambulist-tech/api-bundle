<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;

use function array_filter;

trait GetFieldsFromParameterBag
{
    protected function doGetFields(ParameterBag $bag): array
    {
        return array_filter($bag->all('fields'));
    }
}
