<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Controllers;

use RuntimeException;
use Somnambulist\Bundles\ApiBundle\Response\ResponseConverter;
use Somnambulist\Bundles\ApiBundle\Response\Types\CollectionType;
use Somnambulist\Bundles\ApiBundle\Response\Types\ObjectType;
use Somnambulist\Bundles\ApiBundle\Response\Types\PagerfantaType;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function in_array;
use function sprintf;

/**
 * @method JsonResponse collection(CollectionType $binding)
 * @method JsonResponse item(ObjectType $binding)
 * @method JsonResponse paginate(PagerfantaType $binding)
 */
abstract class ApiController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            ResponseConverter::class,
        ]);
    }

    protected function responseConverter(): ResponseConverter
    {
        return $this->container->get(ResponseConverter::class);
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['collection', 'paginate', 'item'])) {
            return $this->responseConverter()->toJson(...$arguments);
        }

        throw new RuntimeException(sprintf('Method "%s" not found on "%s"', $name, static::class));
    }

    /**
     * Generate the absolute URL for the passed request; useful for PagerfantaType responses
     *
     * @param Request|FormRequest $request
     * @param array               $routeParams
     *
     * @return string
     */
    protected function getAbsoluteUrlForRequest(Request|FormRequest $request, array $routeParams = []): string
    {
        return $this->generateUrl(
            $request->attributes->get('_route'),
            array_merge($routeParams, $request->query->all()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
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
     * @param mixed  $identifier Anything that can be cast to a string
     * @param string $message The response string; can contain a single %s for the identifier
     *
     * @return JsonResponse
     */
    protected function deleted(mixed $identifier, string $message = 'Record with identifier "%s" deleted successfully'): JsonResponse
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
