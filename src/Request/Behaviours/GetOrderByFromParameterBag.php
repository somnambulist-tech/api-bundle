<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Somnambulist\Bundles\ApiBundle\Request\Filters\ConvertOrderByToArray;
use Symfony\Component\HttpFoundation\ParameterBag;

use function is_null;

trait GetOrderByFromParameterBag
{
    protected function doGetOrderBy(ParameterBag $bag, string $default = null): array
    {
        if ($bag->has('sort')) {
            $order = $bag->get('sort', '');
        } else {
            $order = $bag->get('order', '');
        }

        $fields = $this->convertOrderByStringToArrayValues($order);

        if (empty($fields) && !is_null($default)) {
            return $this->convertOrderByStringToArrayValues($default);
        }

        return $fields;
    }

    protected function convertOrderByStringToArrayValues(string $string): array
    {
        return (new ConvertOrderByToArray())($string);
    }
}
