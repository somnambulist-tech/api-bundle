<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services;

use LogicException;
use Somnambulist\Bundles\ApiBundle\Services\Builders\BodyBuilder;
use Somnambulist\Bundles\ApiBundle\Services\Builders\ComponentBuilder;
use Somnambulist\Bundles\ApiBundle\Services\Builders\ParameterBuilder;
use Somnambulist\Bundles\ApiBundle\Services\Builders\ResponseBuilder;
use Somnambulist\Components\Collection\MutableCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

use function array_filter;
use function sprintf;
use function str_replace;
use function strtolower;

class OpenApiGenerator
{
    private ComponentBuilder $components;
    private ResponseBuilder $responses;
    private ParameterBuilder $parameters;
    private BodyBuilder $body;
    private MutableCollection $tagGroups;

    public function __construct(
        private RouterInterface $router,
        private RuleConverters $converters,
        private array $config = []
    ) {
        $this->components = new ComponentBuilder();
        $this->responses  = new ResponseBuilder();
        $this->parameters = new ParameterBuilder($this->converters);
        $this->body       = new BodyBuilder($this->converters);
        $this->tagGroups  = new MutableCollection(['api' => new MutableCollection(), 'models' => new MutableCollection()]);
    }

    public function discover(): MutableCollection
    {
        $paths      = new MutableCollection();
        $routes     = $this->router->getRouteCollection()->all();
        $components = $this->components->build($this->config['path']);

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

            $this->assertRouteSecuritySchemesExist($route, $components);

            $this->createPathItemFromRoute($paths, $route);
        }

        return $this->createDocumentBody(
            $paths,
            $components,
            $this->createTagsFromConfiguration($components),
            $this->createSecurityFromConfiguration()
        );
    }

    private function assertRouteSecuritySchemesExist(Route $route, MutableCollection $components): void
    {
        $security = $route->getDefault('security');

        if (empty($security)) {
            return;
        }

        foreach ($security as $scheme => $requirements) {
            if (!$components->securitySchemes->has($scheme)) {
                throw new LogicException(
                    sprintf('Route "%s" has referenced security scheme "%s" but this is not configured', $route->getPath(), $scheme)
                );
            }
        }
    }

    private function createPathItemFromRoute(MutableCollection $paths, Route $route): void
    {
        foreach ($route->getMethods() as $method) {
            $meta    = $route->getDefault('methods')[strtolower($method)] ?? [];
            $summary = $meta['summary'] ?? null;
            $desc    = $meta['description'] ?? null;
            $opId    = $meta['operationId'] ?? null;
            $dep     = $meta['deprecated'] ?? null;

            if (!$summary && !$desc && !$opId) {
                $summary = $route->getPath();
            }

            $paths->get($route->getPath())->set(strtolower($method), array_filter([
                'tags'        => $t = (array)$route->getDefault('tags'),
                'operationId' => $opId,
                'summary'     => $summary,
                'description' => $desc,
                'deprecated'  => $dep,
                'parameters'  => $this->parameters->build($route),
                'responses'   => $this->responses->build($route),
                'requestBody' => $this->body->build($route),
                'security'    => empty($security) ? null : [$security],
            ]));

            $this->tagGroups->api->merge($t);
        }
    }

    private function createTagsFromConfiguration(MutableCollection $components): array
    {
        $ret = [];

        foreach ($this->config['tags'] ?? [] as $tag => $desc) {
            $ret[] = ['name' => $tag, 'description' => $desc];
        }

        foreach ($components->schemas as $f => $c) {
            $ret[] = [
                'name'          => $n = sprintf('%s_model', strtolower($f)),
                'description'   => '<SchemaDefinition schemaRef="#/components/schemas/' . $f . '"  />',
                'x-displayName' => str_replace('.', ' ', $f),
            ];

            $this->tagGroups->models->add($n);
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

    private function createDocumentBody(
        MutableCollection $paths,
        MutableCollection $components,
        array $tags,
        array $security
    ): MutableCollection {
        return new MutableCollection(array_filter([
            'openapi'     => '3.0.3',
            'info'        => [
                'title'       => $this->config['title'],
                'version'     => $this->config['version'],
                'description' => $this->config['description'],
            ],
            'tags'        => $tags,
            'x-tagGroups' => [
                [
                    'name' => 'API',
                    'tags' => $this->tagGroups->api->unique()->sortBy('values')->values()->toArray(),
                ],
                [
                    'name' => 'Models',
                    'tags' => $this->tagGroups->models->unique()->sortBy('values')->values()->toArray(),
                ],
            ],
            'paths'       => $paths,
            'components'  => $components,
            'security'    => $security,
        ]));
    }
}
