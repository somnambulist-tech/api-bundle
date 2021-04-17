<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;
use function min;

/**
 * Trait GetPaginationFromParameterBag
 *
 * @package    Somnambulist\Bundles\ApiBundle\Request\Behaviours
 * @subpackage Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetPaginationFromParameterBag
 */
trait GetPaginationFromParameterBag
{

    private int $perPage;
    private int $maxPerPage;
    private int $limit;

    private function doGetPage(ParameterBag $bag): int
    {
        $page = $bag->get('page', 1);

        return (int)($page < 1 ? 1 : $page);
    }

    private function doGetPerPage(ParameterBag $bag, int $default = null, int $max = null): int
    {
        $limit = $bag->get('per_page', $default = $this->ensureValueIsInteger($default, $this->perPage));

        return (int)($limit < 1 ? $default : min($limit, $this->ensureValueIsInteger($max, $this->maxPerPage)));
    }

    private function doGetLimit(ParameterBag $bag, int $default = null, int $max = null): int
    {
        $limit = $bag->get('limit', $default = $this->ensureValueIsInteger($default, $this->limit));

        return (int)($limit < 1 ? $default : min($limit, $this->ensureValueIsInteger($max, $this->maxPerPage)));
    }

    private function doGetOffset(ParameterBag $bag, int $limit = null): int
    {
        return (int)($this->doGetPage($bag) - 1) * $this->ensureValueIsInteger($limit, $this->limit);
    }

    private function ensureValueIsInteger(?int $value, int $default): int
    {
        return $value ?? $default;
    }
}
