<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours;

trait ConvertOperator
{
    protected function convertOperator(string $operator): string
    {
        return match ($operator) {
            'eq' => '=',
            'neq' => '!=',
            'lt' => '<',
            'lte' => '<=',
            'gt' => '>',
            'gte' => '>=',
            'in' => 'IN',
            '!in', 'nin' => 'NOT IN',
            'like' => 'LIKE',
            '!like', 'nlike' => 'NOT LIKE',
            'null' => 'IS NULL',
            '!null' => 'IS NOT NULL',
        };
    }
}
