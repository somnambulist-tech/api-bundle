<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use DateTimeInterface;

class DateTimeConverter extends DateConverter
{
    public function rule(): string
    {
        return 'datetime';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return parent::apply($schema, $rule, DateTimeInterface::RFC3339_EXTENDED, $rules);
    }
}
