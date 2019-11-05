<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Response;

use Closure;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\DoctrinePaginatorAdapter;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Serializer\SerializerAbstract;
use Pagerfanta\Pagerfanta;
use RuntimeException;
use Somnambulist\ApiBundle\Services\Transformer\TransformerBinding;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Stopwatch\Stopwatch;
use function array_fill_keys;
use function class_exists;
use function get_class;
use function http_build_query;
use function in_array;
use function parse_str;
use function parse_url;
use function sprintf;
use function ucfirst;

/**
 * Class ResponseFactory
 *
 * Creates JSON responses from a TransformerBinding object.
 *
 * Based on Dingo API Transformer/Factory and Http/Response classes.
 *
 * @package    Somnambulist\ApiBundle\Services\Response
 * @subpackage Somnambulist\ApiBundle\Services\Response\ResponseFactory
 *
 * To aid profiling the following pass through methods can start/stop the bound
 * Stopwatch instance:
 *
 * @method void start(string $segment)
 * @method void stop(string $segment)
 */
final class ResponseFactory
{

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * @var SerializerAbstract
     */
    private $serializer;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * Constructor.
     *
     * @param Manager        $fractal
     * @param Stopwatch|null $stopwatch
     */
    public function __construct(Manager $fractal, Stopwatch $stopwatch = null)
    {
        $this->fractal   = $fractal;
        $this->stopwatch = $stopwatch;
    }

    public function setSerializer(SerializerAbstract $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }

    public function array(TransformerBinding $binding): array
    {
        return $this->createData($this->processIncludes($binding)->createResource($binding));
    }

    public function json(TransformerBinding $binding): JsonResponse
    {
        $resource = $this->processIncludes($binding)->createResource($binding);

        return $this->createResponse($resource, $this->createData($resource));
    }

    private function processIncludes(TransformerBinding $binding): self
    {
        $this->profile('fractal.process_includes', function () use ($binding) {
            return $this->fractal->parseIncludes($binding->getIncludes());
        });

        return $this;
    }

    private function createResource(TransformerBinding $binding): ResourceAbstract
    {
        $class = 'League\\Fractal\\Resource\\' . ucfirst($binding->getType());
        /** @var ResourceAbstract $resource */
        $resource = new $class($binding->getResource(), $binding->getTransformer(), $binding->getKey());
        $resource->setMeta($binding->getMeta());

        $this->detectAndBindPaginator($binding, $resource);

        return $resource;
    }

    private function createData(ResourceAbstract $resource): array
    {
        return $this->profile('fractal.create_data_array', function () use ($resource) {
            return $this->fractal->setSerializer($this->serializer)->createData($resource)->toArray();
        });
    }

    private function createResponse(ResourceAbstract $resource, array $data): JsonResponse
    {
        return $this->profile('fractal.create_json_response', function () use ($resource, $data) {
            $response = JsonResponse::create($data)->setEncodingOptions(JSON_UNESCAPED_UNICODE);

            if ($resource instanceof Collection && $resource->hasPaginator()) {
                /** @var PaginatorInterface $paginator */
                $paginator = $resource->getPaginator();

                $header = [];
                if (($paginator->getCurrentPage() - 1) > 0) {
                    $header[] = sprintf('%s; rel="previous"', $paginator->getUrl($paginator->getCurrentPage() - 1));
                }
                if (($paginator->getCurrentPage() + 1) <= $paginator->getLastPage()) {
                    $header[] = sprintf('%s; rel="next"', $paginator->getUrl($paginator->getCurrentPage() + 1));
                }

                $response->headers->add([
                    'X-API-Pagination-TotalResults' => $paginator->getTotal(),
                    'X-API-Pagination-Page'         => $paginator->getCurrentPage(),
                    'X-API-Pagination-PageCount'    => $paginator->getLastPage(),
                    'X-API-Pagination-PageResults'  => $paginator->getCount(),
                    'X-API-Pagination-PageSize'     => $paginator->getPerPage(),
                    'Link'                          => implode(', ', $header),
                ]);
            }

            return $response;
        });
    }

    private function detectAndBindPaginator(TransformerBinding $binding, ResourceAbstract $resource): void
    {
        if (!$resource instanceof Collection) {
            return;
        }

        $routeGenerator = $this->createRouteGenerator($binding);

        if ($binding->getResource() instanceof Pagerfanta) {
            $class = PagerfantaPaginatorAdapter::class;
        } elseif (class_exists(DoctrinePaginator::class) && $binding->getResource() instanceof DoctrinePaginator) {
            $class = DoctrinePaginatorAdapter::class;
        } else {
            throw new RuntimeException(
                sprintf('Invalid paginator instance found; "%s" is not supported', get_class($binding->getResource()))
            );
        }

        $resource->setPaginator(new $class($binding->getResource(), $routeGenerator));
    }

    private function createRouteGenerator(TransformerBinding $binding): Closure
    {
        return function ($page) use ($binding) {
            $query = [];
            $url   = array_merge(
                array_fill_keys(['scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'], null),
                parse_url($binding->getUrl())
            );

            parse_str($url['query'] ?? '', $query);

            $query['page'] = $page;

            return sprintf('%s://%s%s?%s', $url['scheme'], $url['host'], $url['path'], http_build_query($query, '', '&'));
        };
    }

    private function profile(string $segment, callable $callback)
    {
        $this->start($segment);
        $return = $callback();
        $this->stop($segment);

        return $return;
    }

    public function __call($method, $arguments)
    {
        if ($this->stopwatch && in_array($method, ['start', 'stop'])) {
            return $this->stopwatch->{$method}(...$arguments);
        }
        // no-op
    } //@codeCoverageIgnore
}
