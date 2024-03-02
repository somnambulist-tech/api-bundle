<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders;

use InvalidArgumentException;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\Searchable;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours\ConvertOperator;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours\ConvertStringToArray;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;
use function in_array;
use function is_scalar;

/**
 * Converts basic, single field value pairs to expressions
 *
 * Does not support complex clauses, nor other types other than single values and array values
 * either as multiple values per field, or as a comma separated list.
 */
class SimpleApiFilterDecoder implements FilterDecoderInterface
{
    use ConvertStringToArray;
    use ConvertOperator;

    private array $ignoreFields = [
        'fields',
        'include',
        'limit',
        'marker',
        'sort',
        'order',
        'page',
        'per_page',
    ];

    private ?string $filtersQueryName = null;

    public function useFiltersQueryName(?string $key): self
    {
        $this->filtersQueryName = $key;

        return $this;
    }

    public function decode(Searchable $request): CompositeExpression
    {
        $expressions = CompositeExpression::and();

        $filters = $request->query->all($this->filtersQueryName);

        foreach ($filters as $field => $value) {
            if (!$this->filtersQueryName && in_array($field, $this->ignoreFields)) {
                continue;
            }
            if (!is_scalar($value)) {
                throw new InvalidArgumentException('SimpleApiFilterDecoder encountered a non-scalar value for "%s"', $field);
            }

            $expressions->add($this->decodeExpression($field, $value));
        }

        return $expressions;
    }

    private function decodeExpression(string $field, string $value): Expression
    {
        $operator = 'eq';

        if ($this->shouldBeArray($value)) {
            $value = $this->convertToArray($value);
            $operator = 'in';
        }

        return new Expression($field, $this->convertOperator($operator), $value);
    }
}
