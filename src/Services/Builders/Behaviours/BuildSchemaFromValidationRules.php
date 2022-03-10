<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours;

use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;
use function array_filter;
use function array_merge;
use function array_pop;
use function array_shift;
use function explode;
use function implode;
use function is_array;
use function is_numeric;
use function is_object;
use function is_string;
use function trim;

/**
 * Trait BuildSchemaFromValidationRules
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours\BuildSchemaFromValidationRules
 */
trait BuildSchemaFromValidationRules
{
    private RuleConverters $converters;

    private function buildSchemaFromRuleSpec(string $ruleSpec): array
    {
        $schema = [
            'type' => 'string',
        ];

        if ('' === $ruleSpec) {
            return $schema;
        }

        $rules  = $this->parseRuleSpec($ruleSpec);
        $schema = $this->converters->applyAll($rules, $schema, $rules);

        return array_filter($schema, fn($v) => null !== $v);
    }

    private function parseRuleSpec(string $ruleSpec): array
    {
        $parsed = [];

        foreach (explode('~~', $ruleSpec) as $rule) {
            [$name, $params] = str_contains($rule, ':') ? explode(':', $rule, 2) : [$rule, ''];
            $parsed[trim($name)] = trim($params);
        }

        return $parsed;
    }

    private function buildObjectSchema(array $rules): array
    {
        $properties = $this->buildObjectPropertiesSchema($rules);
        $required   = [];

        foreach ($properties as $property => &$schema) {
            if ($schema['#required'] ?? false) {
                $required[] = $property;
                unset($schema['#required']);
            }
        }

        return array_filter([
            'type'       => 'object',
            'required'   => $required,
            'properties' => $properties,
        ]);
    }

    private function buildObjectPropertiesSchema(array $rules): array
    {
        unset($rules['#rule']);
        $schema = [];

        foreach ($rules as $property => $value) {
            if (is_string($value)) {
                // Scalar
                $schema[$property] = $this->buildSchemaFromRuleSpec($value);
                continue;
            }

            $basePropSchema = $this->buildSchemaFromRuleSpec($value['#rule'] ?? '');

            $arraySpec = $value['*'] ?? null;

            if ($arraySpec) {
                // Array
                if (is_string($arraySpec)) {
                    // Array of non-objects
                    $itemSchema = $this->buildSchemaFromRuleSpec($arraySpec);
                } else {
                    // Array of objects
                    $itemSchema = array_merge(
                        $this->buildSchemaFromRuleSpec($arraySpec['#rule'] ?? ''),
                        $this->buildObjectSchema($arraySpec),
                    );
                }
                $propSchema = [
                    'type'  => 'array',
                    'items' => $itemSchema,
                ];
            } else {
                // Object
                $propSchema = $this->buildObjectSchema($value);
            }

            $schema[$property] = array_merge($basePropSchema, $propSchema);
        }

        return $schema;
    }

    private function unFlattenRuleSpecs(array $ruleSpecs): array
    {
        $rules = [];

        foreach ($ruleSpecs as $propertyPath => $ruleSpec) {
            $this->setDeep($rules, $propertyPath, $this->stringifyRuleSpec($ruleSpec));
        }

        return $rules;
    }

    private function stringifyRuleSpec(string|array $ruleSpec): string
    {
        if (is_string($ruleSpec)) {
            return $ruleSpec;
        }

        $specs = [];

        foreach ($ruleSpec as $key => $val) {
            if (is_object($val)) {
                $val = '';
            }
            if (is_numeric($key)) {
                $key = $val;
                $val = '';
            } elseif (is_array($val)) {
                $val = implode(',', $val);
            }
            if ($key) {
                $specs[] = $key . ($val ? ':' : '') . $val;
            }
        }

        return implode('~~', $specs);
    }

    private function setDeep(array &$data, string $path, mixed $value)
    {
        $keys    = explode('.', $path);
        $lastKey = array_pop($keys);
        $temp    = &$data;

        while ($keys) {
            $key        = array_shift($keys);
            $temp[$key] ??= [];
            if (is_string($temp[$key])) {
                $temp[$key] = [
                    '#rule' => $temp[$key],
                ];
            }
            $temp = &$temp[$key];
        }

        $temp[$lastKey] = $value;
    }
}
