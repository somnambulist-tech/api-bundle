<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Subscribers;

use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Somnambulist\ApiBundle\Services\Converters\ExceptionConverterInterface;
use Somnambulist\ApiBundle\Services\Converters\GenericConverter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function array_key_exists;
use function get_class;

/**
 * Class ConvertExceptionToJSONResponseSubscriber
 *
 * @package Somnambulist\ApiBundle\Subscribers
 * @subpackage Somnambulist\ApiBundle\Subscribers\ConvertExceptionToJSONResponseSubscriber
 */
class ConvertExceptionToJSONResponseSubscriber implements EventSubscriberInterface, ContainerAwareInterface, LoggerAwareInterface
{

    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var array
     */
    private $converters = [];

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * Constructor.
     *
     * @param array $converters
     * @param bool  $debug
     */
    public function __construct(array $converters = [], bool $debug = false)
    {
        $this->converters = $converters;
        $this->debug      = $debug;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onException(ExceptionEvent $event): void
    {
        $e       = $event->getException();
        $data    = $this->getConverterFor($e)->convert($e);
        $payload = $data['data'];

        if ($this->debug) {
            $payload['debug'] = [
                'error' => $e->getMessage(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];

            if ($e->getPrevious()) {
                $payload['debug']['previous'] = [
                    'error' => $e->getPrevious()->getMessage(),
                    'trace' => explode("\n", $e->getPrevious()->getTraceAsString()),
                ];
            }
        }

        $this->logger->error($e->getMessage(), [
            'path'   => $event->getRequest()->getPathInfo(),
            'method' => $event->getRequest()->getMethod(),
            'from'   => $event->getRequest()->getClientIp(),
        ]);

        $event->setResponse(JsonResponse::create($payload, $data['code'])->setEncodingOptions(JSON_UNESCAPED_UNICODE));
    }

    private function getConverterFor(Exception $e): ExceptionConverterInterface
    {
        if (array_key_exists($type = get_class($e), $this->converters)) {
            $class     = $this->converters[$type];
            $converter = $this->container->get($class, ContainerInterface::NULL_ON_INVALID_REFERENCE);

            if ($converter instanceof ExceptionConverterInterface) {
                return $converter;
            }

            return new $class();
        }

        return new GenericConverter();
    }
}
