<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders;

use Somnambulist\Bundles\ApiBundle\Request\Contracts\Searchable;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours\ConvertOperator;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours\ConvertStringToArray;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\ExpressionInterface;
use function explode;
use function is_numeric;
use function str_contains;

/**
 * Converts the somnambulist/api-client compound nested array format to expressions
 *
 * Supports complex nested AND, OR style filters, along with multiple comparison types. This is similar
 * to the nested array format, except it reduces the size of the arguments and compounds operators with
 * values to reduce the overall query string payload.
 */
class CompoundNestedArrayFilterDecoder implements FilterDecoderInterface
{
    use ConvertStringToArray;
    use ConvertOperator;

    public function decode(Searchable $request): CompositeExpression
    {
        $filters     = $request->filters();

        if (empty($filters)) {
            return CompositeExpression::and();
        }

        $expressions = $this->getCompositeContainer($filters);
        $expressions->addAll($this->processParts($filters));

        return $expressions;
    }

    private function processParts(array $parts): array
    {
        $expressions = [];

        foreach ($parts['parts'] as $k => $part) {
            $expression = $this->processPart($k, $part);

            if ($expression instanceof CompositeExpression) {
                $expression->addAll($this->processParts($part));
            }

            $expressions[] = $expression;
        }

        return $expressions;
    }

    private function processPart(mixed $key, mixed $part): ExpressionInterface
    {
        if (!is_numeric($key) && is_string($key) && is_string($part)) {
            if (str_contains($part, ':')) {
                [$operator, $value] = explode(':', $part);
            } else {
                $operator = 'eq';
                $value = $part;
            }

            return new Expression(
                $key,
                $this->convertOperator($operator),
                $this->shouldBeArray($value) ? $this->convertToArray($value) : $value
            );
        }

        return $this->getCompositeContainer($part);
    }

    private function getCompositeContainer(array $part): CompositeExpression
    {
        return $part['type'] === 'or' ? CompositeExpression::or() : CompositeExpression::and();
    }
}
