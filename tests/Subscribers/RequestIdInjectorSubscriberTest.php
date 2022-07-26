<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Subscribers;

use Ramsey\Uuid\Uuid;
use Somnambulist\Bundles\ApiBundle\Subscribers\RequestIdInjectorSubscriber;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\BootTestClient;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\MakeJsonRequest;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelEvents;
use function array_keys;
use function file;
use function file_exists;

class RequestIdInjectorSubscriberTest extends WebTestCase
{

    use BootTestClient;
    use MakeJsonRequest;

    /**
     * @group infrastructure
     * @group support
     * @group support-request-id
     */
    public function testGetEvents()
    {
        $this->assertEquals(
            [KernelEvents::REQUEST, KernelEvents::RESPONSE, KernelEvents::TERMINATE],
            array_keys(RequestIdInjectorSubscriber::getSubscribedEvents())
        );
    }

    /**
     * @group infrastructure
     * @group support
     * @group support-request-id
     */
    public function testSetsRequestIdOnRequest()
    {
        $payload = [
            'foo' => 'bar',
            'bob' => [
                'var1' => 'var2',
                'var3' => 'var4',
            ]
        ];

        $client = $this->makeRequestTo('/json/payload', 'POST', [
            'json' => $payload,
        ]);

        $request = $client->getRequest();

        $this->assertTrue($request->headers->has('X-Request-Id'));
        $this->assertNotNull($request->headers->get('X-Request-Id'));
        $this->assertTrue(Uuid::isValid($request->headers->get('X-Request-Id')));
    }

    /**
     * @group infrastructure
     * @group support
     * @group support-request-id
     */
    public function testSetsRequestIdOnResponse()
    {
        $payload = [
            'foo' => 'bar',
            'bob' => [
                'var1' => 'var2',
                'var3' => 'var4',
            ]
        ];

        $client = $this->makeRequestTo('/json/payload', 'POST', [
            'json' => $payload,
        ]);

        $response = $client->getResponse();

        $this->assertTrue($response->headers->has('X-Request-Id'));
        $this->assertNotNull($response->headers->get('X-Request-Id'));
        $this->assertTrue(Uuid::isValid($response->headers->get('X-Request-Id')));
    }

    /**
     * @group infrastructure
     * @group support
     * @group support-request-id
     */
    public function testRequestIdIsSameAsResponse()
    {
        $payload = [
            'foo' => 'bar',
            'bob' => [
                'var1' => 'var2',
                'var3' => 'var4',
            ]
        ];

        $client = $this->makeRequestTo('/json/payload', 'POST', [
            'json' => $payload,
        ]);

        $request  = $client->getRequest();
        $response = $client->getResponse();

        $this->assertNotNull($request->headers->get('X-Request-Id'));
        $this->assertNotNull($response->headers->get('X-Request-Id'));
        $this->assertEquals($request->headers->get('X-Request-Id'), $response->headers->get('X-Request-Id'));
    }

    /**
     * @group infrastructure
     * @group support
     * @group support-request-id-c
     */
    public function testRequestIdIsPreservedIfPassedIn()
    {
        $payload = [
            'foo' => 'bar',
            'bob' => [
                'var1' => 'var2',
                'var3' => 'var4',
            ]
        ];

        $client = $this->makeRequestTo('/json/payload', 'POST', [
            'json' => $payload,
            'server' => [
                'HTTP_X-Request-Id' => 'bob-foo-bar',
            ]
        ]);

        $request  = $client->getRequest();
        $response = $client->getResponse();

        $this->assertEquals('bob-foo-bar', $request->headers->get('X-Request-Id'));
        $this->assertEquals('bob-foo-bar', $response->headers->get('X-Request-Id'));
    }

    /**
     * @group infrastructure
     * @group support
     * @group support-request-id
     */
    public function testRequestIdIsAvailableToMonolog()
    {
        if (file_exists(static::$kernel->getLogDir() . '/test.log')) {
            unlink(static::$kernel->getLogDir() . '/test.log');
        }

        /** @var Logger $monolog */
        $monolog = static::getContainer()->get('logger');

        $proc = $monolog->getProcessors()[0];

        $this->assertInstanceOf(RequestIdInjectorSubscriber::class, $proc);

        $this->makeRequestTo('/json/payload', 'POST', [
            'json' => ['foo' => 'bar'],
        ]);

        $line = file(static::$kernel->getLogDir() . '/test.log')[0];

        $this->assertMatchesRegularExpression('/^\[([a-fA-F0-9]{8}-(?:[a-fA-F0-9]{4}-){3}[a-fA-F0-9]{12}){1}\] /', $line);
    }
}
