<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_keys;
use function array_merge;
use function implode;
use function sprintf;

class TypeToFormatConverter extends AbstractRuleConverter
{
    private array $map = [
        'uuid'    => 'uuid',
        'integer' => 'int64',
        'float'   => 'double',
        'email'   => 'email',
        'ipv4'    => 'ipv4',
        'ipv6'    => 'ipv6',
        'ip'      => 'ip',
        'url'     => 'url',
    ];

    public function rule(): string
    {
        return sprintf('/^(?:%s)$/', implode('|', array_keys($this->map)));
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, ['format' => $this->map[$rule]]);
    }
}
