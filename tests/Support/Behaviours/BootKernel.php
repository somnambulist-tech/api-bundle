<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours;

use Psr\Container\ContainerInterface;

use function method_exists;

trait BootKernel
{

    protected ?ContainerInterface $dic;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->dic = static::$kernel->getContainer();

        if (method_exists($this, 'setUpTests')) {
            $this->setUpTests();
        }
    }

    protected function tearDown(): void
    {
        if (method_exists($this, 'tearDownTests')) {
            $this->tearDownTests();
        }

        $this->dic = null;

        parent::tearDown();
    }
}
