<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Builders;

use Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours\BuildSchemaFromValidationRules;
use Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours\GetFormRequestFromRoute;
use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;
use Somnambulist\Components\Collection\MutableCollection;
use Symfony\Component\Routing\Route;
use function array_filter;
use function array_merge;
use function array_pop;
use function array_shift;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_null;
use function is_numeric;
use function is_object;
use function is_string;
use function str_contains;

/**
 * Class ParameterBuilder
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\Builders
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\Builders\ParameterBuilder
 */
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

                $qp = array_filter([
                    'name'      => $param,
                    'in'        => 'query',
                    'required'  => str_contains($rules, 'required'),
                    'schema'    => $schema['properties'][$param] ?? ['type' => 'string'],
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
