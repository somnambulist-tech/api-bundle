<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;
use function array_combine;
use function array_map;
use function array_reduce;
use function count;

/**
 * Trait GetNullOrValueFromParameterBag
 *
 * @package    Somnambulist\Bundles\ApiBundle\Request\Behaviours
 * @subpackage Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetNullOrValueFromParameterBag
 */
trait GetNullOrValueFromParameterBag
{
    private function doNullOrValue(ParameterBag $bag, array $fields, string $class = null, bool $subNull = false)
    {
        if (count($fields) === 1 && !$class) {
            return $bag->get(...$fields);
        }

        $allFieldsExists = (array_reduce($fields, fn ($c, $f) => $c + (int)$bag->has($f)) === count($fields));

        if (!$subNull and !$allFieldsExists) {
            return null;
        }

        if ($class) {
            return new $class(...array_map(fn ($f) => $bag->get($f), $fields));
        }

        return array_combine($fields, array_map(fn ($f) => $bag->get($f), $fields));
    }
}
