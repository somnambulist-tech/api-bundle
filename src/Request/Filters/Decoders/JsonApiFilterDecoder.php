<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders;

use Somnambulist\Bundles\ApiBundle\Request\Contracts\Searchable;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours\ConvertStringToArray;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;

/**
 * Converts a JSON API filter query argument into an expression object
 *
 * JSON API does not support OR, or complex nested criteria expressions. The result will be an
 * AND style query with field/value that are always `=`, unless there was an array of values
 * then it will be IN.
 */
class JsonApiFilterDecoder implements FilterDecoderInterface
{
    use ConvertStringToArray;

    public function decode(Searchable $request): CompositeExpression
    {
        $expressions = CompositeExpression::and();

        foreach ($request->filters() as $field => $value) {
            $operator = '=';

            if ($this->shouldBeArray($value)) {
                $value = $this->convertToArray($value);
                $operator = 'IN';
            }

            $expressions->add(new Expression($field, $operator, $value));
        }

        return $expressions;
    }
}
