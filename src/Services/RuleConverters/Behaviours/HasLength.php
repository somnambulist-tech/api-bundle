<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters\Behaviours;

use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function in_array;

trait HasLength
{
    protected function hasLength(array $schema, RuleConverters $converters): bool
    {
        return
            in_array($schema['type'], ['array', 'string'])
            ||
            $converters->has('digits') || $converters->has('digits_between')
        ;
    }
}
