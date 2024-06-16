<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use IlluminateAgnostic\Str\Support\Str;
use InvalidArgumentException;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression as APIExpression;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;
use function array_keys;
use function array_map;
use function str_replace;

/**
 * Applies an API CompositeExpression to a DBAL query builder
 *
 * Providers a mechanism to take a filter request provided to the API and apply the decoded filters
 * to the provided query builder. The query builder should be pre-configured with appropriate FROM
 * clauses before using this builder.
 *
 * A suggested way is to extend and pre-configure the builder with the appropriate field to table
 * mappings making instantiation easier. The field mappings are required and should map each supported
 * field to the appropriate database table / column.
 *
 * As API expressions are limited, this will only convert: =, != >, >=, <, <=, IN, NOT IN, LIKE, NOT LIKE
 * IS NULL, and IS NOT NULL.
 *
 * All values are added using named placeholders, and the values pre-assigned to the query builder.
 *
 * Sometimes you may wish to have better flexibility for a specific field. For example: maybe a "name"
 * field should not perform equals matches, but instead use LIKE. This can be achieved using the
 * second constructor argument. If provided, the alternative operator will be used over the decoded
 * one from the API Expression. This is useful for SimpleDecoder and JSON API that do not allow for
 * defining the comparison type. OpenStack and the nested decoders already allow for operators.
 */
class ApplyApiExpressionsToDBALQueryBuilder
{
    private int $argCounter = 0;

    public function __construct(
        private readonly array $fieldMappings,
        private readonly array $fieldOperatorMappings = [],
    ) {
    }

    public function apply(APIExpression $where, QueryBuilder $qb): void
    {
        $this->argCounter = 0;

        if (!$where->count()) {
            return;
        }

        $expr = $this->buildExpression($where, $qb);

        $qb->where($expr);
    }

    private function buildExpression(APIExpression $where, QueryBuilder $qb): CompositeExpression
    {
        $parts = [];

        foreach ($where->parts() as $part) {
            if ($part instanceof APIExpression) {
                $parts[] = $this->buildExpression($part, $qb);
            } else {
                $values = $this->mapValuesToPlaceholders($part);
                $method = $this->mapOperatorToMethod($part->field, $part->operator);

                foreach ($values as $k => $v) {
                    $qb->setParameter($k, $v);
                }

                if ($part->isNullable()) {
                    $parts[] = $qb->expr()->$method($this->mapField($part->field));
                    continue;
                }
                if ($part->isArray()) {
                    $parts[] = $qb->expr()->$method(
                        $this->mapField($part->field),
                        array_map(fn ($v) => ':' . $v, array_keys($values))
                    );
                    continue;
                }

                if ('comparison' === $method) {
                    $parts[] = $qb->expr()->comparison(
                        $this->mapField($part->field),
                        $this->mapFieldOperator($part->field, $part->operator),
                        ':' . array_keys($values)[0]
                    );
                } else {
                    $parts[] = $qb->expr()->$method($this->mapField($part->field), ':' . array_keys($values)[0]);
                }
            }
        }

        return $where->isOr() ? CompositeExpression::or(...$parts) : CompositeExpression::and(...$parts);
    }

    private function mapOperatorToMethod(string $field, string $operator): string
    {
        return match ($this->mapFieldOperator($field, $operator)) {
            '=' => 'eq',
            '!=' => 'neq',
            '<' => 'lt',
            '<=' => 'lte',
            '>' => 'gt',
            '>=' => 'gte',
            'IN' => 'in',
            'NOT IN' => 'notIn',
            'LIKE' => 'like',
            'NOT LIKE' => 'notLike',
            'IS NULL' => 'isNull',
            'IS NOT NULL' => 'isNotNull',
            'ILIKE', 'NOT ILIKE' => 'comparison',
            default => 'comparison',
        };
    }

    private function mapField(string $field): string
    {
        return $this->fieldMappings[$field] ?? throw new InvalidArgumentException(
            sprintf('API field "%s" has no DBAL column mapping defined', $field)
        );
    }

    private function mapFieldOperator(string $field, string $operator): string
    {
        return $this->fieldOperatorMappings[$field] ?? $operator;
    }

    private function mapValuesToPlaceholders(Expression $part): array
    {
        $field = $this->mapField($part->field);

        if ($part->isArray()) {
            return $this->mapArrayValues($field, $part->value);
        }
        if ($part->isNullable()) {
            return [];
        }

        return [$this->makePlaceholder($field) => $part->value];
    }

    private function mapArrayValues(string $field, array $values): array
    {
        $ret = [];

        foreach ($values as $v) {
            $ret[$this->makePlaceholder($field)] = $v;
        }

        return $ret;
    }

    private function makePlaceholder(string $field): string
    {
        return Str::slug(
            sprintf(
                '%s_%s',
                str_replace('.', '_', $field),
                $this->argCounter++
            ),
            '_'
        );
    }
}
