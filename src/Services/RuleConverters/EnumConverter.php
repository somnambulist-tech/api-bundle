<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use Somnambulist\Components\Domain\Entities\AbstractEnumeration;
use function array_filter;
use function array_map;
use function array_values;
use function explode;
use function is_subclass_of;

/**
 * Class EnumConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\EnumConverter
 */
class EnumConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'enum';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        if (is_subclass_of($params, AbstractEnumeration::class)) {
            $schema['enum'] = array_values($params::values());
        } else {
            $values = $params ? array_map('trim', explode(',', $params)) : [];
            $schema['enum'] = array_values(array_filter($values, 'strlen'));
        }

        return $schema;
    }
}
