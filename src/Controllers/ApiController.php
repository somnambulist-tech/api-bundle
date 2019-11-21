<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Controllers;

use RuntimeException;
use Somnambulist\ApiBundle\Services\Request\RequestArgumentHelper;
use Somnambulist\ApiBundle\Services\Response\ResponseFactory;
use Somnambulist\ApiBundle\Services\Transformer\TransformerBinding;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function in_array;
use function sprintf;

/**
 * Class ApiController
 *
 * Provides access into the RequestArgumentHelper and the ResponseFactory for creating
 * standard JSON responses with headers.
 *
 * @package    Somnambulist\ApiBundle\Controllers
 * @subpackage Somnambulist\ApiBundle\Controllers\ApiController
 *
 * @method JsonResponse collection(TransformerBinding $binding)
 * @method JsonResponse item(TransformerBinding $binding)
 * @method JsonResponse paginate(TransformerBinding $binding)
 *
 * @method array includes(Request $request)
 * @method int page(Request $request, int $default = 1)
 * @method int perPage(Request $request, int $default = null, int $max = null)
 * @method int limit(Request $request, int $default = null, int $max = null)
 * @method int offset(Request $request, int $limit = null)
 * @method mixed nullOrValue(ParameterBag $request, array $fields, string $class = null)
 */
abstract class ApiController extends AbstractController
{

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            ResponseFactory::class,
            RequestArgumentHelper::class,
        ]);
    }

    protected function responseFactory(): ResponseFactory
    {
        return $this->get(ResponseFactory::class);
    }

    protected function requestArgumentHelper(): RequestArgumentHelper
    {
        return $this->get(RequestArgumentHelper::class);
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['collection', 'paginate', 'item'])) {
            return $this->responseFactory()->json(...$arguments);
        }
        if (in_array($name, ['includes', 'page', 'perPage', 'limit', 'offset', 'nullOrValue'])) {
            return $this->requestArgumentHelper()->{$name}(...$arguments);
        }

        throw new RuntimeException(sprintf('Method "%s" not found on "%s"', $name, static::class));
    }

    /**
     * Respond with a created response
     *
     * @param TransformerBinding $binding
     *
     * @return JsonResponse
     */
    protected function created(TransformerBinding $binding): JsonResponse
    {
        return $this->item($binding)->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Respond with an updated resource and OK response
     *
     * @param TransformerBinding $binding
     *
     * @return JsonResponse
     */
    protected function updated(TransformerBinding $binding): JsonResponse
    {
        return $this->item($binding)->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Respond with a No Content response after a successful delete request
     *
     * @param string $identifier
     *
     * @return JsonResponse
     */
    protected function deleted($identifier): JsonResponse
    {
        return JsonResponse::create(
            ['message' => sprintf('Record with identifier "%s" deleted successfully', $identifier)],
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Respond with no content and header if the resource is found but has no content
     *
     * @return JsonResponse
     */
    protected function noContent(): JsonResponse
    {
        return JsonResponse::create([], Response::HTTP_NO_CONTENT);
    }
}
