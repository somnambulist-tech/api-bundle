<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use Somnambulist\Bundles\ApiBundle\Services\Contracts\RuleConverterInterface;
use Somnambulist\Bundles\ApiBundle\Services\Contracts\UsesRuleConvertersInterface;
use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;
use function preg_match;
use function str_starts_with;

/**
 * Class AbstractRuleConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\AbstractRuleConverter
 */
abstract class AbstractRuleConverter implements RuleConverterInterface, UsesRuleConvertersInterface
{
    protected ?RuleConverters $converters = null;

    public function __invoke(array $schema, string $rule, string $params, array $rules): array
    {
        return $this->apply($schema, $rule, $params, $rules);
    }

    public function setConverters(RuleConverters $converters): void
    {
        $this->converters = $converters;
    }

    public function supports(string $rule): bool
    {
        return $this->rule() === $rule || (str_starts_with($this->rule(), '/^') && preg_match($this->rule(), $rule));
    }
}
