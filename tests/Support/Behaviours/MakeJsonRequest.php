<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Behaviours;

use Somnambulist\Domain\Utils\EntityAccessor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Trait MakeJsonRequest
 *
 * @package    Somnambulist\ApiBundle\Tests\Support
 * @subpackage Somnambulist\ApiBundle\Tests\Support\Behaviours\MakeJsonRequest
 */
trait MakeJsonRequest
{

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return mixed
     */
    protected function routeTo($name, array $parameters = [])
    {
        return static::$container->get('router')->getGenerator()->generate($name, $parameters);
    }

    /**
     * Makes a request into the Kernel, checks the reponse status code and returns the payload
     *
     * @param string $uri
     * @param string $method
     * @param array  $payload
     * @param int    $expectedStatusCode
     *
     * @return mixed
     */
    protected function makeJsonRequestTo($uri, $method = 'GET', array $payload = [], $expectedStatusCode = 200)
    {
        $content = null;
        $files   = $server = [];
        $client  = $this->client;

        if (isset($payload['json'])) {
            $content = json_encode($payload['json']);
            $payload = [];
        }

        $client->request($method, $uri, $payload, $files, $server, $content);
        $response = $client->getResponse();

        if ($response->getStatusCode() != $expectedStatusCode) {
            dump(json_decode($response->getContent(), true));
        }

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());

        return json_decode($response->getContent(), true);
    }
}
