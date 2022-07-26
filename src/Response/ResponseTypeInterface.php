<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response;

use League\Fractal\Resource\ResourceAbstract;

interface ResponseTypeInterface
{
    public function asResource(): ResourceAbstract;

    public function getIncludes(): array;

    public function getMeta(): array;
}
