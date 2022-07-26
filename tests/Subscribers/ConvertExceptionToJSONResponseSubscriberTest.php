<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Subscribers;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Subscribers\ConvertExceptionToJSONResponseSubscriber;
use Symfony\Component\HttpKernel\KernelEvents;

class ConvertExceptionToJSONResponseSubscriberTest extends TestCase
{

    public function testEvents()
    {
        $this->assertEquals([KernelEvents::EXCEPTION => ['onException', 10]], ConvertExceptionToJSONResponseSubscriber::getSubscribedEvents());
    }
}
