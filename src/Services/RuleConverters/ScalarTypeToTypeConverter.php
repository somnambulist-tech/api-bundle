<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_keys;
use function array_merge;
use function implode;
use function sprintf;

/**
 * Class ScalarTypeToTypeConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\ScalarTypeToTypeConverter
 */
class ScalarTypeToTypeConverter extends AbstractRuleConverter
{
    private array $map = [
        'boolean' => 'boolean',
        'numeric' => 'number',
        'array'   => 'array',
        'integer' => 'integer',
    ];

    public function rule(): string
    {
        return sprintf('/^(?:%s)$/', implode('|', array_keys($this->map)));
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, ['type' => $this->map[$rule]]);
    }
}
