<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Builders;

use Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours\BuildSchemaFromValidationRules;
use Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours\GetFormRequestFromRoute;
use Somnambulist\Bundles\ApiBundle\Services\RuleConverters;
use Symfony\Component\Routing\Route;
use function array_filter;
use function in_array;
use function is_null;

/**
 * Class BodyBuilder
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\Builders
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\Builders\BodyBuilder
 */
class BodyBuilder
{
    use BuildSchemaFromValidationRules;
    use GetFormRequestFromRoute;

    public function __construct(RuleConverters $converters)
    {
        $this->converters = $converters;
    }

    public function build(Route $route): array
    {
        if (in_array('GET', $route->getMethods())) {
            return [];
        }

        if (null === $req = $this->getFormRequestFromMethodArguments($route)) {
            return [];
        }

        return $this->buildRequestBodySchemaFromRuleSpecs(
            $req->rules(),
            !is_null($req->__meta__['examples']) ? $req->__meta__['examples']->invoke($req) : [],
        );
    }

    private function buildRequestBodySchemaFromRuleSpecs(array $ruleSpecs, array $examples = []): array
    {
        $hasRequired = false;
        $contentType = 'application/x-www-form-urlencoded';

        foreach ($ruleSpecs as $ruleSpec) {
            $ruleSpec    = '~~' . $this->stringifyRuleSpec($ruleSpec) . '~~';
            $hasRequired = $hasRequired || str_contains($ruleSpec, '~~required~~') || str_contains($ruleSpec, '~~present~~');

            if (str_contains($ruleSpec, '~~uploaded_file')) {
                $contentType = 'multipart/form-data';
            }
        }

        $rules  = $this->unFlattenRuleSpecs($ruleSpecs);
        $schema = $this->buildObjectSchema($rules);

        return [
            'required' => $hasRequired,
            'content'  => array_filter([
                $contentType => array_filter([
                    'schema'   => $schema,
                    'examples' => $examples,
                ]),
            ]),
        ];
    }
}
