<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Expression;

use ArrayAccess;
use Countable;

use function array_key_exists;
use function count;

/**
 * Borrowed from Doctrine\DBAL\Query\Expression\CompositeExpression
 *
 * Holds the expressions to use for filtering along with the type (and or, or) allowing for easier
 * conversion to SQL or a.n.other query language.
 */
class CompositeExpression implements Countable, ArrayAccess, ExpressionInterface
{
    public const TYPE_AND = 'and';
    public const TYPE_OR = 'or';

    private function __construct(
        private readonly string $type,
        private array $parts = [])
    {
        $this->addAll($parts);
    }

    public static function and(array $parts = []): self
    {
        return new self(self::TYPE_AND, $parts);
    }

    public static function or(array $parts = []): self
    {
        return new self(self::TYPE_OR, $parts);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->parts);
    }

    public function offsetGet($offset): mixed
    {
        return $this->parts[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->parts[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->parts[$offset]);
    }

    public function isOr(): bool
    {
        return self::TYPE_OR === $this->type;
    }

    public function isAnd(): bool
    {
        return self::TYPE_AND === $this->type;
    }

    public function addAll(array $parts = []): self
    {
        foreach ($parts as $part) {
            $this->add($part);
        }

        return $this;
    }

    public function add($part): self
    {
        if (empty($part)) {
            return $this;
        }

        if ($part instanceof self && count($part) === 0) {
            return $this;
        }

        $this->parts[] = $part;

        return $this;
    }

    public function count(): int
    {
        return count($this->parts);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getParts(): array
    {
        return $this->parts;
    }
}
