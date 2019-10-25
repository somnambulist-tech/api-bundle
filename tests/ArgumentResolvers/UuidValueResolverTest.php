<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\ArgumentResolvers;

use Somnambulist\Domain\Utils\IdentityGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function json_decode;

/**
 * Class UuidValueResolverTest
 *
 * @package Somnambulist\ApiBundle\Tests\ArgumentResolvers
 * @subpackage Somnambulist\ApiBundle\Tests\ArgumentResolvers\UuidValueResolverTest
 * @group controller
 * @group controller-argument-resolvers
 */
class UuidValueResolverTest extends WebTestCase
{

    /**
     * @group exception-subscriber
     * @group debug
     */
    public function testCanCastToUuid()
    {
        $client = static::createClient(['debug' => false]);
        $client->request('GET', '/test/resolvers/' . $id = IdentityGenerator::new()->toString());
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($id, $data['value']);
    }
}
