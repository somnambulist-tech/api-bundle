<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Subscribers;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Somnambulist\ApiBundle\Response\ExceptionConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function explode;
use function get_class;

/**
 * Class ConvertExceptionToJSONResponseSubscriber
 *
 * @package Somnambulist\ApiBundle\Subscribers
 * @subpackage Somnambulist\ApiBundle\Subscribers\ConvertExceptionToJSONResponseSubscriber
 */
class ConvertExceptionToJSONResponseSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{

    use LoggerAwareTrait;

    private ExceptionConverter $converter;
    private bool $debug;

    public function __construct(ExceptionConverter $converter, bool $debug = false)
    {
        $this->converter = $converter;
        $this->debug     = $debug;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $e       = $event->getThrowable();
        $data    = $this->converter->convert($e);
        $payload = $data['data'];

        if ($this->debug) {
            $payload['debug'] = [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];

            if ((null !== $prev = $e->getPrevious()) && $prev !== $e) {
                $payload['debug']['previous'] = [
                    'message' => $prev->getMessage(),
                    'class' => get_class($prev),
                    'trace' => explode("\n", $prev->getTraceAsString()),
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
