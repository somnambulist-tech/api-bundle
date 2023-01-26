<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Contracts;

interface HasIncludes
{
    public function includes(): array;
}
