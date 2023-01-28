<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders;

use Somnambulist\Bundles\ApiBundle\Request\Contracts\Searchable;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours\ConvertOperator;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours\ConvertStringToArray;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;

use function explode;
use function in_array;
use function str_contains;

/**
 * Converts an OpenStack API filter query argument into an expression object
 *
 * OpenStack allows for in/not in as query args with comma separated values in the query string.
 * OR is not supported. Complex nested AND conditions are reduced to single elements in a unified
 * AND e.g. (x = y AND y = z) AND (a >= b) is functionally decoded to (x = y AND y = z AND a >= b)
 * as they are equivalent and OpenStack does not specify nesting operations.
 */
class OpenStackApiFilterDecoder implements FilterDecoderInterface
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

    public function decode(Searchable $request): CompositeExpression
    {
        $expressions = CompositeExpression::and();

        foreach ($request->query() as $field => $value) {
            if (in_array($field, $this->ignoreFields)) {
                continue;
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            foreach ($value as $item) {
                $expressions->add($this->decodeExpression($field, $item));
            }
        }

        return $expressions;
    }

    private function decodeExpression(string $field, string $value): Expression
    {
        $operator = 'eq';

        if (str_contains($value, ':')) {
            [$operator, $value] = explode(':', $value);
        }
        if ($this->shouldBeArray($value)) {
            $value = $this->convertToArray($value);
        }

        return new Expression($field, $this->convertOperator($operator), $value);
    }
}
