<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use Somnambulist\Bundles\ApiBundle\Services\RuleConverters\Behaviours\HasLength;
use function array_merge;

/**
 * Class MaxConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\MaxConverter
 */
class MaxConverter extends AbstractRuleConverter
{
    use HasLength;

    public function rule(): string
    {
        return 'max';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        $schema[$this->hasLength($schema, $this->converters) ? 'maxLength' : 'maximum'] = ctype_digit($params) ? (int)$params : $params;

        return $schema;
    }
}
