<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\ArgumentResolvers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function json_decode;

/**
 * Class UuidValueResolverTest
 *
 * @package Somnambulist\Bundles\ApiBundle\Tests\ArgumentResolvers
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\ArgumentResolvers\UuidValueResolverTest
 * @group controller
 * @group controller-argument-resolvers
 */
class ExternalIdentityValueResolverTest extends WebTestCase
{

    /**
     * @group exception-subscriber
     * @group debug
     */
    public function testCanCastToExternalIdentity()
    {
        $client = static::createClient(['debug' => false]);
        $client->request('GET', '/test/resolvers/external_id?provider=my_provider&identity=some-identity-string');
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('my_provider', $data['provider']);
        $this->assertEquals('some-identity-string', $data['identity']);
    }
}
