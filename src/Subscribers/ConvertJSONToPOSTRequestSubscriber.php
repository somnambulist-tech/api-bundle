<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Subscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function is_array;
use function is_null;
use function json_decode;

/**
 * Class ConvertJSONToPOSTRequestSubscriber
 *
 * @package Somnambulist\Bundles\ApiBundle\Subscribers
 * @subpackage Somnambulist\Bundles\ApiBundle\Subscribers\ConvertJSONToPOSTRequestSubscriber
 */
class ConvertJSONToPOSTRequestSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10],
        ];
    }

    public function onRequest(RequestEvent $event)
    {
        if (!is_null($data = json_decode($event->getRequest()->getContent(), true))) {
            $event->getRequest()->request->replace(is_array($data) ? $data : []);
        }
    }
}
