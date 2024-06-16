<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Expression;

use function is_array;

class Expression implements ExpressionInterface, FieldInterface
{
    public function __construct(
        public readonly string $field,
        public readonly string $operator,
        public readonly mixed $value
    ) {
    }

    public function isArray(): bool
    {
        return is_array($this->value);
    }

    public function isNullable(): bool
    {
        return in_array($this->operator, ['IS NULL', 'IS NOT NULL']);
    }
}
