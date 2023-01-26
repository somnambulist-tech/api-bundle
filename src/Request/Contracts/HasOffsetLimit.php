<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Contracts;

interface HasOffsetLimit
{
    public function offset(): int;
    public function limit(): int;
}
