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
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function explode;
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function is_null;
use function json_decode;
use function sprintf;
use function str_contains;
use function strtolower;
use function substr_count;
use const ARRAY_FILTER_USE_BOTH;

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

    public function __construct(RouterInterface $router, array $config = [])
    {
        $this->router = $router;
        $this->config = $config;
    }

    public function discover(): MutableCollection
    {
        $routes     = $this->router->getRouteCollection()->all();
        $paths      = new MutableCollection();
        $components = new MutableCollection(['schemas' => new MutableCollection()]);

        $this->createComponentsFromConfigPath($components);

        foreach ($routes as $route) {
            if (true !== $route->getDefault('document')) {
                continue;
            }

            if (!$paths->has($route->getPath())) {
                $paths->set($route->getPath(), new MutableCollection());
            }

            foreach ($route->getMethods() as $method) {
                if (str_contains($route->getDefault('_controller'), '::')) {
                    [$class, $opId] = $this->getClassAndMethodFromController($route);
                    $opId = Str::lower($method) . Str::studly($opId);
                } else {
                    $class = $route->getDefault('_controller');
                    $opId  = Str::lower($method) . Str::afterLast($class, '\\');
                }

                if ($route->getDefault('operation')) {
                    $opId = $route->getDefault('operation');
                }

                $paths->get($route->getPath())->set(strtolower($method), array_filter([
                    'tags'        => (array)$route->getDefault('tags'),
                    'operationId' => $opId,
                    'summary'     => $route->getDefault('summary'),
                    'description' => $route->getDefault('description'),
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
            'paths'      => $paths,
            'components' => $components,
        ]);
    }

    private function getClassAndMethodFromController(Route $route): array
    {
        if (str_contains($route->getDefault('_controller'), '::')) {
            return explode('::', $route->getDefault('_controller'));
        }

        return [$route->getDefault('_controller'), '__invoke'];
    }

    private function getFromRequestFromMethodArguments(Route $route): ?FormRequest
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

        if (null !== $req = $this->getFromRequestFromMethodArguments($route)) {
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

        if (null === $req = $this->getFromRequestFromMethodArguments($route)) {
            return [];
        }

        $rules    = implode(' ', array_map(fn ($v) => is_array($v) ? implode(' ', $v) : $v, array_values($req->rules())));
        $required = array_keys(array_filter($req->rules(), fn ($v, $k) => !str_contains($k, '.') && str_contains(is_array($v) ? implode(' ', $v) : $v, 'required'), ARRAY_FILTER_USE_BOTH));

        return [
            'required' => str_contains($rules, 'required'),
            'content'  => array_filter([
                (str_contains($rules, 'uploaded_file') ? 'multipart/form-data' : 'application/x-www-form-urlencoded') => array_filter([
                    'schema'   => [
                        'type'       => 'object',
                        'required'   => $required,
                        'properties' => $this->createPropertiesDefinitionFromFormRequest($req),
                    ],
                    'examples' => $req instanceof HasOpenApiExamples ? $req->examples() : [],
                ]),
            ]),
        ];
    }

    private function createPropertiesDefinitionFromFormRequest(FormRequest $req): array
    {
        $props = [];

        foreach ($req->rules() as $param => $rules) {
            if (is_array($rules)) {
                $rules = implode(' ', $rules);
            }

            $def = $this->createFieldDefinitionFromRule($rules, false);

            if (str_contains($param, '*')) {
                if (substr_count($param, '*') > 1) {
                    throw new LogicException(sprintf('Param "%s" is deeply nested. This is not currently supported', $param));
                }

                [$parent, $field] = explode('.', $param, 2);
                $key = Str::afterLast($field, '.');

                if (!array_key_exists($parent, $props)) {
                    throw new LogicException(
                        sprintf(
                            'FormRequest "%s" has a nested rule "%s" that is defined after "%s". The "array" rule must be defined first.',
                            $req::class, $param, $parent,
                        )
                    );
                }

                if ($field == '*') {
                    // everything is all the same
                    $props[$parent]['items']['type'] = 'string';
                    continue;
                } else {
                    $props[$parent]['items']['type']             = 'object';
                    $props[$parent]['items']['required']         = array_merge(($props[$parent]['items']['required'] ?? []), (str_contains($rules, 'required') ? [$key] : []));
                    $props[$parent]['items']['properties'][$key] = $this->createFieldDefinitionFromRule($rules, false);

                    $props[$parent]['items'] = array_filter($props[$parent]['items']);
                    continue;
                }
            } else {
                $props[$param] = $def;
            }
        }

        return $props;
    }

    private function createFieldDefinitionFromRule(string $rule, bool $addRequired = true): array
    {
        $def = array_filter([
            'type'     => 'string',
            'format'   => $this->getFormatFromRule($rule),
            'required' => $addRequired ? str_contains($rule, 'required') : null,
        ]);

        if (str_contains($rule, 'array')) {
            // initialise the type to array and prepare an items container
            $def['type']  = 'array';
            $def['items'] = [];
        }

        return $def;
    }

    private function getFormatFromRule(string $rule): ?string
    {
        switch (true):
            case str_contains($rule, 'uuid'): return 'uuid';
            case str_contains($rule, 'uploaded_file'): return 'binary';
            case str_contains($rule, 'datetime'): return 'date-time';
            case str_contains($rule, 'date'): return 'full-date';
            case str_contains($rule, 'float'): return 'double';
            case str_contains($rule, 'integer'): return 'int64';
        endswitch;

        return null;
    }

    private function createComponentsFromConfigPath(MutableCollection $components): void
    {
        $files = (new Finder())->files()->ignoreDotFiles(true)->in($this->config['path'])->name(['*.json', '*.yaml']);

        foreach ($files as $file) {
            switch ($file->getExtension()) {
                case 'json': $components->get('schemas')->set($file->getBasename('.json'), json_decode($file->getContents(), true)); break;
                case 'yaml': $components->get('schemas')->set($file->getBasename('.yaml'), Yaml::parse($file->getContents())); break;
            }
        }
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

            $responses[$code] = [
                'description' => sprintf('A %s to be returned', explode('/', $template)[1]),
                'content'     => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => sprintf('#/components/%s', $template),
                        ],
                    ],
                ],
            ];
        }

        return $responses;
    }
}
