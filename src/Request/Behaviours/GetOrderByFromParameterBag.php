<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;

use function array_filter;
use function explode;
use function is_null;
use function mb_strtoupper;
use function str_contains;
use function substr;
use function trim;

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
        $fields = [];

        foreach (array_filter(explode(',', $string)) as $field) {
            $dir = 'asc';

            if (str_contains($field, ':')) {
                [$field, $dir] = explode(':', $field);
            }

            if (str_starts_with(trim($field), '-')) {
                $field = substr(trim($field), 1);
                $dir = 'desc';
            }

            $fields[trim($field)] = mb_strtoupper(trim($dir));
        }

        return $fields;
    }
}
