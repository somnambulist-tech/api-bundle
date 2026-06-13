<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\ArgumentResolvers;

use PHPUnit\Framework\Attributes\Group;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function json_decode;

#[Group("controller")]
#[Group("controller-argument-resolvers")]
class ExternalIdentityValueResolverTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    #[Group("exception-subscriber")]
    #[Group("debug")]
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
