<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services;

use Doctrine\Instantiator\Instantiator;
use IlluminateAgnostic\Str\Support\Str;
use LogicException;
use ReflectionClass;
use Somnambulist\Bundles\ApiBundle\Services\Contracts\HasOpenApiExamples;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Components\Collection\MutableCollection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function array_pop;
use function array_shift;
use function array_values;
use function class_exists;
use function ctype_alpha;
use function ctype_digit;
use function explode;
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function is_callable;
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
    private array $config;
    private array $ruleHandlers = [];

    public function __construct(RouterInterface $router, array $config = [])
    {
        $this->router = $router;
        $this->config = $config;

        $this->buildDefaultRuleHandlers();
    }

    public function discover(): MutableCollection
    {
        $routes     = $this->router->getRouteCollection()->all();
        $paths      = new MutableCollection();
        $components = new MutableCollection(['schemas' => new MutableCollection()]);

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

            foreach ($route->getMethods() as $method) {
                $meta = $route->getDefault('methods')[Str::lower($method)] ?? [];
                $summ = $meta['summary'] ?? null;
                $desc = $meta['description'] ?? null;
                $opId = $meta['operationId'] ?? null;
                $dep  = $meta['deprecated'] ?? null;

                if (!$summ && !$desc && !$opId) {
                    $summ = $route->getPath();
                }

                $paths->get($route->getPath())->set(strtolower($method), array_filter([
                    'tags'        => (array)$route->getDefault('tags'),
                    'operationId' => $opId,
                    'summary'     => $summ,
                    'description' => $desc,
                    'deprecated'  => $dep,
                    'parameters'  => $this->getRouteParameters($route),
                    'responses'   => $this->getResponses($route),
                    'requestBody' => $this->getBodyParametersFromMethodSignature($route),
                ]));
            }
        }

        return new MutableCollection([
            'openapi'    => '3.0.3',
            'info'       => [
                'title'       => $this->config['title'],
                'version'     => $this->config['version'],
                'description' => $this->config['description'],
            ],
            'tags'       => $this->createTagsFromConfiguration(),
            'paths'      => $paths,
            'components' => $components,
        ]);
    }

    private function createTagsFromConfiguration(): array
    {
        $ret = [];

        foreach ($this->config['tags'] ?? [] as $tag => $desc) {
            $ret[] = ['name' => $tag, 'description' => $desc];
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
                return (new Instantiator())->instantiate((string)$parameter->getType());
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
                if ($req instanceof HasOpenApiExamples) {
                    $example = $req->examples()[$param] ?? null;

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

        $normalized = [];
        foreach ($req->rules() as $prop => $spec) {
            $normalized[$prop] = $this->normalizeRuleSpec($spec);
        }
        $bigRulesString = implode(' ', $normalized);

        $required = [];
        foreach ($normalized as $prop => $spec) {
            if (str_contains($spec, 'required')) {
                $required[] = $prop;
            }
        }

        $contentType = str_contains($bigRulesString, 'uploaded_file') ?
            'multipart/form-data' :
            'application/x-www-form-urlencoded';

        return [
            'required' => str_contains($bigRulesString, 'required'),
            'content'  => array_filter([
                $contentType => array_filter([
                    'schema'   => array_filter([
                        'type'       => 'object',
                        'required'   => $required,
                        'properties' => $this->createPropertiesDefinitionFromFormRequest($req),
                    ]),
                    'examples' => $req instanceof HasOpenApiExamples ? $req->examples() : [],
                ]),
            ]),
        ];
    }

    private function createPropertiesDefinitionFromFormRequest(FormRequest $req): array
    {
        return $this->buildPropertiesSchema($this->normalizeRules($req->rules()));
    }

    private function createComponentsFromConfigPath(MutableCollection $components): void
    {
        $files = (new Finder())->files()->ignoreDotFiles(true)->in($this->config['path'])->name(['*.json', '*.yaml']);

        foreach ($files as $file) {
            $path = str_replace([$this->config['path'] . DIRECTORY_SEPARATOR, '.json', '.yaml', '\\'], ['', '', '', '/'], $file->getRealPath());
            [$refType, $schema] = explode('/', $path, 2);
            $schema = str_replace('/', '.', $schema);

            switch ($file->getExtension()) {
                case 'json': $components->get($refType)->set($schema, json_decode($this->resolveComponentReferencesInSchema($file->getContents()), true)); break;
                case 'yaml': $components->get($refType)->set($schema, Yaml::parse($this->resolveComponentReferencesInSchema($file->getContents()))); break;
            }
        }
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

    private function buildPropertiesSchema(array $rules): array
    {
        $schema = [];
        foreach ($rules as $key => $value) {
            if ('#rule' === $key) {
                continue;
            }
            if (is_string($value)) { // Property
                $schema[$key] = $this->buildSchemaFromRule($value);
            } else {
                $schema[$key] = $this->buildSchemaFromRule($value['#rule'] ?? '');
                if (isset($value['*'])) { // Array
                    $schema[$key]['type'] = 'array';
                    if (is_string($value['*'])) { // Array of Values
                        $items = $this->buildSchemaFromRule($value['*']);
                    } else { // Array of Objects
                        $items               = $this->buildSchemaFromRule($value['*']['#rule'] ?? '');
                        $items['type']       = 'object';
                        $items['properties'] = $this->buildPropertiesSchema($value['*']);
                    }
                    $schema[$key]['items'] = $items;
                } else { // Object
                    $schema[$key]['type']       = 'object';
                    $schema[$key]['properties'] = $this->buildPropertiesSchema($value);
                }
            }
        }

        return $schema;
    }

    private function buildSchemaFromRule(string $ruleSpec): array
    {
        $schema = [
            'type' => 'string',
        ];

        if ('' === $ruleSpec) {
            return $schema;
        }

        $rules = $this->parseValidationRuleSpec($ruleSpec);

        $ruleHandlers = [];
        foreach (array_keys($rules) as $rule) {
            if (isset($this->ruleHandlers[$rule])) {
                $ruleHandlers[$rule] ??= [];
                $ruleHandlers[$rule][] = $this->ruleHandlers[$rule];
            }
            foreach ($this->ruleHandlers as $rx => $value) {
                if (!ctype_alpha($rx[0]) && preg_match($rx, $rule)) {
                    $ruleHandlers[$rule] ??= [];
                    $ruleHandlers[$rule][] = $value;
                }
            }
        }

        foreach ($ruleHandlers as $rule => $handlers) {
            foreach ($handlers as $handler) {
                $schema = $handler($schema, $rule, $rules[$rule], $rules, $this->ruleHandlers);
            }
        }

        return array_filter($schema, fn ($v) => null !== $v);
    }

    private function parseValidationRuleSpec(string $ruleSpec): array
    {
        static $cache = [];

        if (!isset($cache[$ruleSpec])) {
            $parsed = [];
            foreach (explode('|', $ruleSpec) as $rule) {
                [$name, $params] = str_contains($rule, ':') ? explode(':', $rule, 2) : [$rule, ''];
                $parsed[trim($name)] = trim($params);
            }

            return $cache[$ruleSpec] = $parsed;
        }

        return $cache[$ruleSpec];
    }

    private function normalizeRules(array $rawRules): array
    {
        $rules = [];
        foreach ($rawRules as $propertyPath => $ruleSpec) {
            $this->setDeep($rules, $propertyPath, $this->normalizeRuleSpec($ruleSpec));
        }

        return $rules;
    }

    private function normalizeRuleSpec(string|array $ruleSpec): string
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

    private function setDeep(&$data, $path, $value)
    {
        $keys    = explode('.', $path);
        $lastKey = array_pop($keys);
        $temp    = &$data;

        while ($keys) {
            $key = array_shift($keys);
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

    private function buildDefaultRuleHandlers()
    {
        // Map validation rule to schema `type`.
        $ruleTypeMap = [
            'boolean' => 'boolean',
            'numeric' => 'number',
            'array'   => 'array',
            'integer' => 'integer',
        ];
        $ruleNames = implode('|', array_keys($ruleTypeMap));
        $this->ruleHandlers["/^($ruleNames)$/"] = function (
            array $schema,
            string $rule,
        ) use ($ruleTypeMap) {
            return array_merge($schema, ['type' => $ruleTypeMap[$rule]]);
        };

        // Map validation rule to schema `format`.
        $ruleFormatMap = [
            'uuid'          => 'uuid',
            'integer'       => 'int64',
            'email'         => 'email',
            'ipv4'          => 'ipv4',
            'ipv6'          => 'ipv6',
            'ip'            => 'ip',
            'url'           => 'url',
            'uploaded_file' => 'binary',
        ];
        $ruleNames = implode('|', array_keys($ruleFormatMap));
        $this->ruleHandlers["/^($ruleNames)$/"] = function (
            array $schema,
            string $rule,
        ) use ($ruleFormatMap) {
            return array_merge($schema, ['format' => $ruleFormatMap[$rule]]);
        };

        $this->ruleHandlers['/date/'] = function (array $schema) {
            return array_merge($schema, ['format' => 'date-time']);
        };

        $this->ruleHandlers['required'] = function (array $schema) {
            return array_merge($schema, ['required' => true]);
        };

        $this->ruleHandlers['min'] = function (
            array $schema,
            string $rule,
            string $params,
            array $rules,
        ) {
            $hasLength =
                ('string' === $schema['type'] || 'array' === $schema['type']) ||
                (isset($rules['digits']) || isset($rules['digits_between']));
            $key = match ($rule) {
                'min' => $hasLength ? 'minLength' : 'minimum',
                'max' => $hasLength ? 'maxLength' : 'maximum',
            };
            $schema[$key] = ctype_digit($params) ? (int)$params : $params;

            return $schema;
        };

        $this->ruleHandlers['max'] = $this->ruleHandlers['min'];

        $this->ruleHandlers['between'] = function (
            array $schema,
            string $rule,
            string $params,
            array $rules,
            array $handlers,
        ) {
            [$min, $max] = explode(',', $params);
            $schema = $handlers['min']($schema, 'min', $min, $rules);

            return $handlers['max']($schema, 'max', $max, $rules);
        };

        $this->ruleHandlers['digits_between'] = $this->ruleHandlers['between'];

        $this->ruleHandlers['nullable'] = function (array $schema) {
            return array_merge($schema, ['nullable' => true]);
        };

        $this->ruleHandlers['accepted'] = function (array $schema) {
            return array_merge($schema, [
                'type'  => null,
                'oneOf' => [
                    ['type' => 'boolean'],
                    ['type' => 'string', 'enum' => ['on', 'yes', '1', 'true']],
                ],
            ]);
        };

        $this->ruleHandlers['enum'] = function (array $schema, string $rule, string $params) {
            if (class_exists($params) && is_callable([$params, 'values'])) {
                $schema['enum'] = array_values($params::values());
            }

            return $schema;
        };
    }
}
