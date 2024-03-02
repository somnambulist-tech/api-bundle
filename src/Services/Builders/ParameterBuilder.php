<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Builders;

use Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours\BuildSchemaFromValidationRules;
use Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours\GetFormRequestFromRoute;
use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;
use Symfony\Component\Routing\Route;
use function array_filter;
use function array_merge;
use function implode;
use function in_array;
use function is_array;
use function is_null;
use function str_contains;

class ParameterBuilder
{
    use BuildSchemaFromValidationRules;
    use GetFormRequestFromRoute;

    public function __construct(RuleConverters $converters)
    {
        $this->converters = $converters;
    }

    public function build(Route $route): array
    {
        return array_merge($this->getRouteParameters($route), $this->getQueryParametersFromMethodSignature($route));
    }

    private function getRouteParameters(Route $route): array
    {
        $params = [];

        foreach ($route->getRequirements() as $param => $rules) {
            $params[] = [
                'name'     => $param,
                'in'       => 'path',
                'required' => true,
                'schema'   => ['type' => 'string'],
            ];
        }

        return $params;
    }

    private function getQueryParametersFromMethodSignature(Route $route): array
    {
        $params = [];

        if (!in_array('GET', $route->getMethods())) {
            return $params;
        }

        if (null !== $req = $this->getFormRequestFromMethodArguments($route)) {
            $schema = $this->buildObjectSchema($this->unFlattenRuleSpecs($req->rules()));

            foreach ($req->rules() as $param => $rules) {
                $exampleKey = 'example';
                $example    = null;

                if (str_contains($param, '.')) {
                    // ignore nested rules, will be in the schema object def
                    continue;
                }
                if (is_array($rules)) {
                    $rules = implode(' ', $rules);
                }
                if (!is_null($req->__meta__['examples'])) {
                    $example = $req->__meta__['examples']->invoke($req)[$param] ?? null;

                    if (is_array($example)) {
                        $exampleKey = 'examples';
                    }
                }

                $type = $schema['properties'][$param] ?? ['type' => 'string'];

                $qp = array_filter([
                    'name'      => $param . ('array' === $type['type'] ? '[]' : ''),
                    'in'        => 'query',
                    'required'  => str_contains($rules, 'required'),
                    'schema'    => $type,
                    $exampleKey => $example,
                ]);

                if (in_array($t = $schema['properties'][$param]['type'] ?? '', ['array', 'object'])) {
                    $qp['explode'] = true;
                    $qp['style'] = $t == 'object' ? 'deepObject' : 'form';
                }

                $params[] = $qp;
            }
        }

        return $params;
    }
}
