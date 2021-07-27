<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services;

use Doctrine\Instantiator\Instantiator;
use IlluminateAgnostic\Str\Support\Str;
use LogicException;
use ReflectionClass;
use Somnambulist\Bundles\ApiBundle\Services\Attributes\OpenApiExamples;
use Somnambulist\Bundles\ApiBundle\Services\Contracts\HasOpenApiExamples;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Components\Collection\MutableCollection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;
use function array_filter;
use function array_map;
use function array_merge;
use function array_pop;
use function array_shift;
use function explode;
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function is_null;
use function is_numeric;
use function is_object;
use function is_string;
use function json_decode;
use function preg_match_all;
use function sprintf;
use function str_contains;
use function str_replace;
use function strtolower;
use function trim;
use const DIRECTORY_SEPARATOR;

/**
 * Class OpenApiGenerator
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\OpenApiGenerator
 */
class OpenApiGenerator
{
    private RouterInterface $router;
    private RuleConverters  $converters;
    private array           $config;

    public function __construct(RouterInterface $router, RuleConverters $converters, array $config = [])
    {
        $this->router     = $router;
        $this->converters = $converters;
        $this->config     = $config;
    }

    public function discover(): MutableCollection
    {
        $routes     = $this->router->getRouteCollection()->all();
        $paths      = new MutableCollection();
        $components = new MutableCollection([
            'schemas'         => new MutableCollection(),
            'responses'       => new MutableCollection(),
            'parameters'      => new MutableCollection(),
            'examples'        => new MutableCollection(),
            'requestBodies'   => new MutableCollection(),
            'headers'         => new MutableCollection(),
            'securitySchemes' => new MutableCollection(),
            'links'           => new MutableCollection(),
            'callbacks'       => new MutableCollection(),
        ]);

        $this->createComponentsFromConfigPath($components);

        foreach ($routes as $routeName => $route) {
            if (true !== $route->getDefault('document')) {
                continue;
            }

            if (!$paths->has($route->getPath())) {
                $paths->set($route->getPath(), new MutableCollection(array_filter([
                    'summary'     => $route->getDefault('summary'),
                    'description' => $route->getDefault('description'),
                ])));
            }

            $security = $route->getDefault('security');

            if ($security) {
                foreach ($security as $scheme => $requirements) {
                    if (!$components->securitySchemes->has($scheme)) {
                        throw new LogicException(
                            sprintf('Route "%s" has referenced security scheme "%s" but this is not configured', $route->getPath(), $scheme)
                        );
                    }
                }
            }

            foreach ($route->getMethods() as $method) {
                $meta     = $route->getDefault('methods')[Str::lower($method)] ?? [];
                $summary  = $meta['summary'] ?? null;
                $desc     = $meta['description'] ?? null;
                $opId     = $meta['operationId'] ?? null;
                $dep      = $meta['deprecated'] ?? null;

                if (!$summary && !$desc && !$opId) {
                    $summary = $route->getPath();
                }

                $paths->get($route->getPath())->set(strtolower($method), array_filter([
                    'tags'        => (array)$route->getDefault('tags'),
                    'operationId' => $opId,
                    'summary'     => $summary,
                    'description' => $desc,
                    'deprecated'  => $dep,
                    'parameters'  => $this->getRouteParameters($route),
                    'responses'   => $this->getResponses($route),
                    'requestBody' => $this->getBodyParametersFromMethodSignature($route),
                    'security'    => [$security],
                ]));
            }
        }

        return new MutableCollection(array_filter([
            'openapi'    => '3.0.3',
            'info'       => [
                'title'       => $this->config['title'],
                'version'     => $this->config['version'],
                'description' => $this->config['description'],
            ],
            'tags'       => $this->createTagsFromConfiguration(),
            'paths'      => $paths,
            'components' => $components,
            'security'   => $this->createSecurityFromConfiguration(),
        ]));
    }

    private function createTagsFromConfiguration(): array
    {
        $ret = [];

        foreach ($this->config['tags'] ?? [] as $tag => $desc) {
            $ret[] = ['name' => $tag, 'description' => $desc];
        }

        return $ret;
    }

    private function createSecurityFromConfiguration(): array
    {
        $ret = [];

        foreach ($this->config['security'] ?? [] as $scheme => $requirements) {
            $ret[] = [$scheme => $requirements];
        }

        return $ret;
    }

    private function getClassAndMethodFromController(Route $route): array
    {
        if (str_contains($route->getDefault('_controller'), '::')) {
            return explode('::', $route->getDefault('_controller'));
        }

        return [$route->getDefault('_controller'), '__invoke'];
    }

    private function getFormRequestFromMethodArguments(Route $route): ?FormRequest
    {
        [$controller, $method] = $this->getClassAndMethodFromController($route);

        $class = new ReflectionClass($controller);

        foreach ($class->getMethod($method)->getParameters() as $parameter) {
            if (is_a((string)$parameter->getType(), FormRequest::class, true)) {
                $ref            = new ReflectionClass((string)$parameter->getType());
                $form           = (new Instantiator())->instantiate((string)$parameter->getType());
                $form->__meta__ = ['examples' => null, 'auth' => []];

                foreach ($ref->getMethods() as $method) {
                    if (!empty($method->getAttributes(OpenApiExamples::class))) {
                        $form->__meta__['examples'] = $method;
                        break;
                    }
                }

                // @todo For BC to be removed at next major version
                if ($form instanceof HasOpenApiExamples) {
                    $form->__meta__['examples'] = $ref->getMethod('examples');
                }

                return $form;
            }
        }

        return null;
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

        return array_merge($params, $this->getQueryParametersFromMethodSignature($route));
    }

    private function getQueryParametersFromMethodSignature(Route $route): array
    {
        $params = [];

        if (!in_array('GET', $route->getMethods())) {
            return $params;
        }

        if (null !== $req = $this->getFormRequestFromMethodArguments($route)) {
            foreach ($req->rules() as $param => $rules) {
                $exampleKey = 'example';
                $example    = null;

                if (is_array($rules)) {
                    $rules = implode(' ', $rules);
                }
                if (!is_null($req->__meta__['examples'])) {
                    $example = $req->__meta__['examples']->invoke($req)[$param] ?? null;

                    if (is_array($example)) {
                        $exampleKey = 'examples';
                    }
                }

                $params[] = array_filter([
                    'name'      => $param,
                    'in'        => 'query',
                    'required'  => str_contains($rules, 'required'),
                    'schema'    => ['type' => 'string'],
                    $exampleKey => $example,
                ]);
            }
        }

        return $params;
    }

    private function getBodyParametersFromMethodSignature(Route $route): array
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
            $ruleSpec    = '|' . $this->stringifyRuleSpec($ruleSpec) . '|';
            $hasRequired = $hasRequired || str_contains($ruleSpec, '|required|') || str_contains($ruleSpec, '|present|');
            if (str_contains($ruleSpec, '|uploaded_file')) {
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

    private function createComponentsFromConfigPath(MutableCollection $components): void
    {
        $files = (new Finder())->files()->ignoreDotFiles(true)->in($this->config['path'])->name(['*.json', '*.yaml']);

        foreach ($files as $file) {
            $path = str_replace([$this->config['path'] . DIRECTORY_SEPARATOR, '.json', '.yaml', '\\'], [
                '', '', '', '/',
            ], $file->getRealPath());
            [$refType, $schema] = explode('/', $path, 2);
            $schema = str_replace('/', '.', $schema);

            switch ($file->getExtension()) {
                case 'json':
                    $components->get($refType)
                        ->set($schema, json_decode($this->resolveComponentReferencesInSchema($file->getContents()), true))
                    ;
                    break;
                case 'yaml':
                    $components->get($refType)->set($schema, Yaml::parse($this->resolveComponentReferencesInSchema($file->getContents())));
                    break;
            }
        }

        $components
            ->filter(fn (MutableCollection $c) => $c->count() === 0)
            ->each(fn (MutableCollection $c, $k) => $components->unset($k))
        ;
    }

    /**
     * Replaces slashed paths with dots as a / after the <schemas> is not allowed in the OpenAPI spec
     *
     * @param string $schema
     *
     * @return string
     */
    private function resolveComponentReferencesInSchema(string $schema): string
    {
        preg_match_all('/"#\/components\/(?<refType>[\w]+)\/(?<schema>.*)"/', $schema, $matches);

        return str_replace($matches['schema'], array_map(fn($s) => str_replace('/', '.', $s), $matches['schema']), $schema);
    }

    private function getResponses(Route $route): array
    {
        if (is_null($route->getDefault('responses'))) {
            throw new LogicException(sprintf('Route "%s" has no responses defined. A response is required.', $route->getPath()));
        }

        $responses = [];

        foreach ($route->getDefault('responses') as $code => $template) {
            if (!$template) {
                $responses[$code]['description'] = 'No response body is returned from this request';
                continue;
            }
            [$refType, $schema] = explode('/', $template, 2);
            $schema = str_replace('/', '.', $schema);

            $responses[$code] = [
                'description' => sprintf('A %s to be returned', $schema),
                'content'     => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => sprintf('#/components/%s/%s', $refType, $schema),
                        ],
                    ],
                ],
            ];
        }

        return $responses;
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
        foreach (explode('|', $ruleSpec) as $rule) {
            [$name, $params] = str_contains($rule, ':') ? explode(':', $rule, 2) : [$rule, ''];
            $parsed[trim($name)] = trim($params);
        }

        return $parsed;
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

        return implode('|', $specs);
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
