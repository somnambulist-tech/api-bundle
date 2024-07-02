<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters\Behaviours;

use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;
use function array_key_exists;
use function in_array;

trait HasLength
{
    protected function hasLength(array $schema, array $rules): bool
    {
        return
            in_array($schema['type'], ['array', 'string'])
            ||
            array_key_exists('digits', $rules) || array_key_exists('digits_between', $rules)
        ;
    }
}
