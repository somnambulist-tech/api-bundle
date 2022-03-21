<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response;

use League\Fractal\Manager as Fractal;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Serializer\SerializerAbstract as Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Stopwatch\Stopwatch;
use function implode;
use function in_array;
use function sprintf;

/**
 * Class ResponseConverter
 *
 * Converts a response type to an appropriate response format.
 *
 * Based on Dingo API Transformer/Factory and Http/Response classes.
 *
 * @package    Somnambulist\Bundles\ApiBundle\Response
 * @subpackage Somnambulist\Bundles\ApiBundle\Response\ResponseConverter
 *
 * To aid profiling the following pass through methods can start/stop the bound
 * Stopwatch instance:
 *
 * @method void start(string $segment)
 * @method void stop(string $segment)
 */
final class ResponseConverter
{
    public function __construct(
        Serializer $serializer,
        private Fractal $fractal,
        private ?Stopwatch $stopwatch = null
    ) {
        $this->setSerializer($serializer);
    }

    public function setSerializer(Serializer $serializer): self
    {
        $this->fractal->setSerializer($serializer);

        return $this;
    }

    public function toArray(ResponseTypeInterface $type): array
    {
        $this->processIncludes($type);
        $this->processFields($type);

        return $this->createData($type->asResource());
    }

    public function toJson(ResponseTypeInterface $type): JsonResponse
    {
        $resource = $type->asResource();

        $this->processIncludes($type);
        $this->processFields($type);

        return $this->createResponse($resource, $this->createData($resource));
    }

    private function processIncludes(ResponseTypeInterface $type): void
    {
        $this->profile('fractal.process_includes', fn() => $this->fractal->parseIncludes($type->getIncludes()));
    }

    private function processFields(ResponseTypeInterface $type): void
    {
        $this->profile('fractal.process_fields', fn() => $this->fractal->parseFieldsets($type->getFields()));
    }

    private function createData(ResourceAbstract $resource): array
    {
        return $this->profile('fractal.create_data_array', fn () => $this->fractal->createData($resource)->toArray());
    }

    private function createResponse(ResourceAbstract $resource, array $data): JsonResponse
    {
        $response = $this->profile('fractal.create_json_response', fn () => (new JsonResponse($data))->setEncodingOptions(JSON_UNESCAPED_UNICODE));

        if ($resource instanceof Collection && $resource->hasPaginator()) {
            $paginator = $resource->getPaginator();
            $header    = [];

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
    }

    private function profile(string $segment, callable $callback): mixed
    {
        $this->start($segment);
        $return = $callback();
        $this->stop($segment);

        return $return;
    }

    public function __call($method, $arguments)
    {
        if ($this->stopwatch && in_array($method, ['start', 'stop'])) {
            $this->stopwatch->{$method}(...$arguments);
        }
    } //@codeCoverageIgnore
}
