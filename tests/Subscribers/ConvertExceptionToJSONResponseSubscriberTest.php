<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Subscribers;

use Somnambulist\ApiBundle\Subscribers\ConvertExceptionToJSONResponseSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ConvertExceptionToJSONResponseSubscriberTest
 *
 * @package Somnambulist\ApiBundle\Tests\Subscribers
 * @subpackage Somnambulist\ApiBundle\Tests\Subscribers\ConvertExceptionToJSONResponseSubscriberTest
 */
class ConvertExceptionToJSONResponseSubscriberTest extends TestCase
{

    public function testEvents()
    {
        $this->assertEquals([KernelEvents::EXCEPTION => 'onException'], ConvertExceptionToJSONResponseSubscriber::getSubscribedEvents());
    }
}
