<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Controllers;

use RuntimeException;
use Somnambulist\ApiBundle\Request\RequestArgumentHelper;
use Somnambulist\ApiBundle\Response\ResponseConverter;
use Somnambulist\ApiBundle\Response\Types\CollectionType;
use Somnambulist\ApiBundle\Response\Types\ObjectType;
use Somnambulist\ApiBundle\Response\Types\PagerfantaType;
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
 * @package    Somnambulist\ApiBundle\Controllers
 * @subpackage Somnambulist\ApiBundle\Controllers\ApiController
 *
 * @method JsonResponse collection(CollectionType $binding)
 * @method JsonResponse item(ObjectType $binding)
 * @method JsonResponse paginate(PagerfantaType $binding)
 *
 * @method array includes(Request $request)
 * @method array orderBy(Request $request, string $default = null)
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
            ResponseConverter::class,
            RequestArgumentHelper::class,
        ]);
    }

    protected function responseConverter(): ResponseConverter
    {
        return $this->get(ResponseConverter::class);
    }

    protected function requestArgumentHelper(): RequestArgumentHelper
    {
        return $this->get(RequestArgumentHelper::class);
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['collection', 'paginate', 'item'])) {
            return $this->responseConverter()->toJson(...$arguments);
        }
        if (in_array($name, ['includes', 'orderBy', 'page', 'perPage', 'limit', 'offset', 'nullOrValue'])) {
            return $this->requestArgumentHelper()->{$name}(...$arguments);
        }

        throw new RuntimeException(sprintf('Method "%s" not found on "%s"', $name, static::class));
    }

    /**
     * Respond with a created response
     *
     * @param ObjectType $type
     *
     * @return JsonResponse
     */
    protected function created(ObjectType $type): JsonResponse
    {
        return $this->item($type)->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Respond with an updated resource and OK response
     *
     * @param ObjectType $type
     *
     * @return JsonResponse
     */
    protected function updated(ObjectType $type): JsonResponse
    {
        return $this->item($type)->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Respond with a No Content response after a successful delete request
     *
     * @param string $identifier A string castable identity
     * @param string $message The response string; can contain a single %s for the identifier
     *
     * @return JsonResponse
     */
    protected function deleted($identifier, string $message = 'Record with identifier "%s" deleted successfully'): JsonResponse
    {
        return new JsonResponse(
            ['message' => sprintf($message, $identifier)],
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
        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
