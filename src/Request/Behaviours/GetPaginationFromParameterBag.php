<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;
use function max;
use function min;

trait GetPaginationFromParameterBag
{
    protected int $perPage = 20;
    protected int $maxPerPage = 100;
    protected int $limit = 100;

    protected function doGetPage(ParameterBag $bag): int
    {
        $page = $bag->get('page', 1);

        return (int)(max($page, 1));
    }

    protected function doGetPerPage(ParameterBag $bag, ?int $default = null, ?int $max = null): int
    {
        $limit = $bag->get('per_page', $default = $this->ensureValueIsInteger($default, $this->perPage));

        return (int)($limit < 1 ? $default : min($limit, $this->ensureValueIsInteger($max, $this->maxPerPage)));
    }

    protected function doGetLimit(ParameterBag $bag, ?int $default = null, ?int $max = null): int
    {
        $limit = $bag->get('limit', $default = $this->ensureValueIsInteger($default, $this->limit));

        return (int)($limit < 1 ? $default : min($limit, $this->ensureValueIsInteger($max, $this->maxPerPage)));
    }

    protected function doGetOffset(ParameterBag $bag, ?int $limit = null): int
    {
        if ($bag->has('offset')) {
            return $bag->getInt('offset') < 1 ? 0 : $bag->getInt('offset');
        }

        return ($this->doGetPage($bag) - 1) * $this->ensureValueIsInteger($limit, $this->limit);
    }

    protected function doGetOffsetMarker(ParameterBag $bag): ?string
    {
        return $bag->get('marker');
    }

    protected function ensureValueIsInteger(?int $value, int $default): int
    {
        return $value ?? $default;
    }
}
