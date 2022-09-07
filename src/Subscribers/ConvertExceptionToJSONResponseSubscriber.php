<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Subscribers;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Somnambulist\Bundles\ApiBundle\Response\ExceptionConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use function explode;
use function get_class;
use function str_starts_with;

class ConvertExceptionToJSONResponseSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private ExceptionConverter $converter,
        private bool $debug = false,
        private string $apiRoot = '/api',
        private string $docRoot = '/api/docs',
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onException', 10],
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $route = $event->getRequest()->getRequestUri();

        if (!str_starts_with($route, $this->apiRoot) || $route === $this->docRoot ) {
            return;
        }

        $e       = $event->getThrowable();
        $data    = $this->converter->convert($e);
        $payload = $data['data'];

        if ($this->debug) {
            $payload['debug'] = [
                'message' => $e->getMessage(),
                'class'   => get_class($e),
                'trace'   => explode("\n", $e->getTraceAsString()),
            ];

            if ((null !== $prev = $e->getPrevious()) && ($prev !== $e && $prev->getMessage() !== $e->getMessage())) {
                $payload['debug']['previous'] = [
                    'message' => $prev->getMessage(),
                    'class'   => get_class($prev),
                    'trace'   => explode("\n", $prev->getTraceAsString()),
                ];
            }
        }

        $this->logger->error($e->getMessage(), [
            'path'   => $event->getRequest()->getPathInfo(),
            'method' => $event->getRequest()->getMethod(),
            'from'   => $event->getRequest()->getClientIp(),
        ]);

        $event->setResponse((new JsonResponse($payload, $data['code']))->setEncodingOptions(JSON_UNESCAPED_UNICODE));
    }
}
