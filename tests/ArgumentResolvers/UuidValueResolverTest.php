<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\ArgumentResolvers;

use PHPUnit\Framework\Attributes\Group;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Kernel;
use Somnambulist\Components\Utils\IdentityGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function json_decode;

#[Group("controller")]
#[Group("controller-argument-resolvers")]
class UuidValueResolverTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    #[Group("exception-subscriber")]
    #[Group("debug")]
    public function testCanCastToUuid()
    {
        $client = static::createClient(['debug' => false]);
        $client->request('GET', '/test/resolvers/' . $id = IdentityGenerator::new()->toString());
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($id, $data['value']);
    }

    #[Group("exception-subscriber")]
    #[Group("debug")]
    public function testCanCastToUuidWithVariousNames()
    {
        $id1 = IdentityGenerator::new()->toString();
        $id2 = IdentityGenerator::new()->toString();
        $id3 = IdentityGenerator::new()->toString();

        $client = static::createClient(['debug' => false]);
        $client->request('GET', sprintf('/test/resolvers/%s/%s/%s', $id1, $id2, $id3));
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($id1, $data['value1']);
        $this->assertEquals($id2, $data['value2']);
        $this->assertEquals($id3, $data['value3']);
    }
}
