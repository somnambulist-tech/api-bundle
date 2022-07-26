<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Subscribers;

use Somnambulist\Bundles\ApiBundle\Subscribers\ConvertJSONToPOSTRequestSubscriber;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\BootTestClient;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\MakeJsonRequest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class ConvertJSONToPOSTRequestSubscriberTest extends WebTestCase
{

    use BootTestClient;
    use MakeJsonRequest;

    /**
     * @group infrastructure
     * @group support
     * @group support-json-prefilter
     */
    public function testGetEvents()
    {
        $this->assertEquals([KernelEvents::REQUEST => ['onRequest', 20]], ConvertJSONToPOSTRequestSubscriber::getSubscribedEvents());
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
