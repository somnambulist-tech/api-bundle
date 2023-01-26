<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Contracts;

interface HasFilters
{
    public function filters(): array;
}
