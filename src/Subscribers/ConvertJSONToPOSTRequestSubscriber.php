<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Subscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use function is_array;
use function is_null;
use function json_decode;

class ConvertJSONToPOSTRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 20],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!is_null($data = json_decode($event->getRequest()->getContent(), true))) {
            $event->getRequest()->request->replace(is_array($data) ? $data : []);
        }
    }
}
