<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours;

use Somnambulist\Bundles\ApiBundle\Tests\Support\Kernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use function method_exists;

/**
 * @method void setKernelClass()
 * @method void setUpTests()
 */
trait BootTestClient
{
    protected ?KernelBrowser $client;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

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
