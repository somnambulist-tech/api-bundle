<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Contracts;

use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

/**
 * Interface UsesRuleConvertersInterface
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\Contracts
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\Contracts\UsesRuleConvertersInterface
 */
interface UsesRuleConvertersInterface
{
    public function setConverters(RuleConverters $converters): void;
}
