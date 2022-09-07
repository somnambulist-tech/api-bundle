<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Contracts;

use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

interface UsesRuleConvertersInterface
{
    public function setConverters(RuleConverters $converters): void;
}
