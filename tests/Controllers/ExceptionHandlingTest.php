<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Controllers;

use PHPUnit\Framework\Attributes\Group;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExceptionHandlingTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    #[Group("exception-subscriber")]
    #[Group("debug")]
    public function testDebugExcludedWhenAppDebugFalse()
    {
        $client = static::createClient(['debug' => false]);
        $client->request('GET', '/test/not_found');
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayNotHasKey('debug', $data);
    }

    #[Group("exception-subscriber")]
    public function testNotFound()
    {
        $client = static::createClient();
        $client->request('GET', '/test/not_found');
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('debug', $data);
    }

    #[Group("exception-subscriber")]
    public function testInvalidDomainState()
    {
        $client = static::createClient();
        $client->request('GET', '/test/invalid_state');
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('debug', $data);
    }

    #[Group("exception-subscriber")]
    public function testPrevious()
    {
        $client = static::createClient();
        $client->request('GET', '/test/previous');
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('previous', $data['debug']);
    }

    #[Group("exception-subscriber")]
    #[Group("assert")]
    public function testAssertions()
    {
        $client = static::createClient();
        $client->request('GET', '/test/assert');
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('errors', $data);
        $this->assertCount(1, $data['errors']);
    }

    #[Group("exception-subscriber")]
    #[Group("assert")]
    public function testLazyAssertions()
    {
        $client = static::createClient();
        $client->request('GET', '/test/assert_lazy');
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('errors', $data);
        $this->assertCount(2, $data['errors']);
    }

    #[Group("exception-subscriber")]
    #[Group("assert")]
    public function testLazyTryAllAssertions()
    {
        $client = static::createClient();
        $client->request('GET', '/test/assert_lazy_try_all');
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('errors', $data);
        $this->assertCount(4, $data['errors']);
    }

    #[Group("exception-subscriber")]
    #[Group("messenger")]
    public function testMessengerException()
    {
        $client = static::createClient();
        $client->request('GET', '/test/messenger');
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('errors', $data);
    }
}
