<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;
use function array_filter;
use function explode;
use function is_null;
use function substr;
use function trim;

/**
 * Trait GetOrderByFromParameterBag
 *
 * @package    Somnambulist\Bundles\ApiBundle\Request\Behaviours
 * @subpackage Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetOrderByFromParameterBag
 */
trait GetOrderByFromParameterBag
{

    protected function doGetOrderBy(ParameterBag $bag, string $default = null): array
    {
        $fields = $this->convertOrderByStringToArrayValues($bag->get('order', ''));

        if (empty($fields) && !is_null($default)) {
            return $this->convertOrderByStringToArrayValues($default);
        }

        return $fields;
    }

    protected function convertOrderByStringToArrayValues(string $string): array
    {
        $fields = [];

        foreach (array_filter(explode(',', $string)) as $field) {
            if (str_starts_with(trim($field), '-')) {
                $fields[substr(trim($field), 1)] = 'DESC';
            } else {
                $fields[trim($field)] = 'ASC';
            }
        }

        return $fields;
    }
}
