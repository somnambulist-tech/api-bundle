<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders;

use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression;
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;

interface FilterDecoderInterface
{
    public function decode(FormRequest $request): CompositeExpression;
}
