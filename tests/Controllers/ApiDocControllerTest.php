<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Controllers;

use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\BootTestClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function file_put_contents;

/**
 * Class ApiDocControllerTest
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Controllers
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Controllers\ApiDocControllerTest
 */
class ApiDocControllerTest extends WebTestCase
{

    use BootTestClient;

    /**
     * @group controllers
     * @group controllers-docs
     */
    public function testDocGenerator()
    {
        $this->client->request('GET', '/docs');

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();

        //file_put_contents(__DIR__ . '/../../var/doc.html', $response->getContent());

        $this->assertStringContainsString('API Documentation', $response->getContent());
        $this->assertStringContainsString('{"openapi":"3.0.3","info":{"title":"API Documentation","version":"1.0.0","description"', $response->getContent());
        $this->assertStringContainsString('Redoc.init(data, {}, document.getElementById(\'openapi-ui\'));', $response->getContent());
    }
}
