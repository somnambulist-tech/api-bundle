<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Subscribers;

use Monolog\Processor\ProcessorInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ResetInterface;
use function is_null;

/**
 * Class RequestIdInjectorSubscriber
 *
 * @package    Somnambulist\Bundles\ApiBundle\Subscribers
 * @subpackage Somnambulist\Bundles\ApiBundle\Subscribers\RequestIdInjectorSubscriber
 */
class RequestIdInjectorSubscriber implements EventSubscriberInterface, ProcessorInterface, ResetInterface
{
    private string $header = 'X-Request-Id';
    private array $data = [];

    public function __construct(string $header = null)
    {
        if (!is_null($header)) {
            $this->header = $header;
        }
    }

    public function __invoke(array $record): array
    {
        if (isset($this->data['request_id']) && !empty($this->data['request_id'])) {
            $record['extra']['request_id'] = $this->data['request_id'];
        }

        return $record;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST   => ['onRequest', 1024],
            KernelEvents::RESPONSE  => 'onResponse',
            KernelEvents::TERMINATE => 'onTerminate',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->headers->has($this->header)) {
            $this->data['request_id'] = $request->headers->get($this->header);
            return;
        }

        $this->data['request_id'] = Uuid::uuid4()->toString();

        $request->headers->set($this->header, $this->data['request_id']);
    }

    public function onResponse(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set($this->header, $this->data['request_id'] ?? null);
    }

    public function onTerminate(TerminateEvent $event): void
    {
        $this->reset();
    }

    public function reset()
    {
        $this->data = [];
    }
}
