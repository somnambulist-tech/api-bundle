<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Subscribers;

use Somnambulist\ApiBundle\Subscribers\ConvertJSONToPOSTRequestSubscriber;
use Somnambulist\ApiBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\ApiBundle\Tests\Support\Behaviours\MakeJsonRequest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ConvertJSONToPOSTRequestSubscriberTest
 *
 * @package Somnambulist\ApiBundle\Tests\Subscribers
 * @subpackage Somnambulist\ApiBundle\Tests\Subscribers\ConvertJSONToPOSTRequestSubscriberTest
 */
class ConvertJSONToPOSTRequestSubscriberTest extends WebTestCase
{

    use BootKernel;
    use MakeJsonRequest;

    /**
     * @group infrastructure
     * @group support
     * @group support-json-prefilter
     */
    public function testGetEvents()
    {
        $this->assertEquals([KernelEvents::REQUEST => 'onRequest'], ConvertJSONToPOSTRequestSubscriber::getSubscribedEvents());
    }

    /**
     * @group infrastructure
     * @group support
     * @group support-json-prefilter
     */
    public function testDecodesJsonPayload()
    {
        $payload = [
            'foo' => 'bar',
            'bob' => [
                'var1' => 'var2',
                'var3' => 'var4',
            ]
        ];

        $response = $this->makeJsonRequestTo('/json/payload', 'POST', [
            'json' => $payload,
        ]);

        $this->assertEquals($payload, $response);
    }
}
