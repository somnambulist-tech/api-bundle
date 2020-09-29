<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Subscribers;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Subscribers\ConvertExceptionToJSONResponseSubscriber;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ConvertExceptionToJSONResponseSubscriberTest
 *
 * @package Somnambulist\Bundles\ApiBundle\Tests\Subscribers
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Subscribers\ConvertExceptionToJSONResponseSubscriberTest
 */
class ConvertExceptionToJSONResponseSubscriberTest extends TestCase
{

    public function testEvents()
    {
        $this->assertEquals([KernelEvents::EXCEPTION => 'onException'], ConvertExceptionToJSONResponseSubscriber::getSubscribedEvents());
    }
}
