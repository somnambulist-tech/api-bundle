<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use function method_exists;

/**
 * @method void setKernelClass()
 * @method void setUpTests()
 */
trait BootTestClient
{

    protected ?KernelBrowser $client;

    protected function setUp(): void
    {
        if (method_exists($this, 'setKernelClass')) {
            self::setKernelClass();
        }

        $this->client = self::createClient();

        if (method_exists($this, 'setUpTests')) {
            $this->setUpTests();
        }
    }
}
