<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait MakeJsonRequest
{

    protected function routeTo(string $name, array $parameters = []): string
    {
        return static::getContainer()->get('router')->getGenerator()->generate($name, $parameters);
    }

    /**
     * Makes a request into the Kernel, checks the response status code and returns the payload
     *
     * @param string $uri
     * @param string $method
     * @param array  $payload
     * @param int    $code
     *
     * @return mixed
     */
    protected function makeJsonRequestTo(string $uri, string $method = 'GET', array $payload = [], int $code = 200)
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

        if ($response->getStatusCode() != $code) {
            dump(json_decode($response->getContent(), true));
        }

        $this->assertEquals($code, $response->getStatusCode());

        return json_decode($response->getContent(), true);
    }

    /**
     * Makes a request into the Kernel, checks the response status code and returns the payload
     *
     * @param string $uri
     * @param string $method
     * @param array  $payload
     * @param int    $code
     *
     * @return KernelBrowser
     */
    protected function makeRequestTo(string $uri, string $method = 'GET', array $payload = [], int $code = 200): KernelBrowser
    {
        $content = null;
        $files   = $server = [];
        $client  = $this->client;

        if (isset($payload['server'])) {
            $server = $payload['server'];
            unset($payload['server']);
        }
        if (isset($payload['json'])) {
            $content = json_encode($payload['json']);
            $payload = [];
        }

        $client->request($method, $uri, $payload, $files, $server, $content);

        $this->assertEquals($code, $client->getResponse()->getStatusCode());

        return $client;
    }
}
