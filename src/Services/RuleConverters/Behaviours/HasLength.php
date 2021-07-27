<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters\Behaviours;

use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;
use function in_array;

/**
 * Trait HasLength
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters\Behaviours
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\Behaviours\HasLength
 */
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
