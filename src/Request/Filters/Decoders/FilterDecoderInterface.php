<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders;

use Somnambulist\Bundles\ApiBundle\Request\Contracts\Searchable;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression;

interface FilterDecoderInterface
{
    public function decode(Searchable $request): CompositeExpression;
}
