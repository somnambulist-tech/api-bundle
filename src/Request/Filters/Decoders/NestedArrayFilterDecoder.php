<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders;

use Somnambulist\Bundles\ApiBundle\Request\Contracts\Searchable;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours\ConvertOperator;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours\ConvertStringToArray;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\ExpressionInterface;

use function array_key_exists;

/**
 * Converts the somnambulist/api-client nested array format to expressions
 *
 * Supports complex nested AND, OR style filters, along with multiple comparison types.
 */
class NestedArrayFilterDecoder implements FilterDecoderInterface
{
    use ConvertStringToArray;
    use ConvertOperator;

    public function decode(Searchable $request): CompositeExpression
    {
        $filters     = $request->filters();
        $expressions = $this->getCompositeContainer($filters);
        $expressions->addAll($this->processParts($filters));

        return $expressions;
    }

    private function processParts(array $parts): array
    {
        $expressions = [];

        foreach ($parts['parts'] as $part) {
            $expression = $this->processPart($part);

            if ($expression instanceof CompositeExpression) {
                $expression->addAll($this->processParts($part));
            }

            $expressions[] = $expression;
        }

        return $expressions;
    }

    private function processPart(array $part): ExpressionInterface
    {
        if (array_key_exists('field', $part)) {
            $value = $part['value'];

            return new Expression(
                $part['field'],
                $this->convertOperator($part['operator']),
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
