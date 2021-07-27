<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_merge;
use function explode;

/**
 * Class AcceptedConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\AcceptedConverter
 */
class AcceptedConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'accepted';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, [
            'type'  => null,
            'oneOf' => [
                ['type' => 'boolean'],
                ['type' => 'string', 'enum' => ['on', 'yes', '1', 'true']],
            ],
        ]);
    }
}
