<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Contracts;

interface HasPagination
{
    public function page(): int;
    public function perPage(): int;
}
