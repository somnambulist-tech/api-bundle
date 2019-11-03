<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Behaviours;

use Psr\Container\ContainerInterface;
use function method_exists;

/**
 * Trait BootKernel
 *
 * @package Somnambulist\ApiBundle\Tests\Support
 * @subpackage Somnambulist\ApiBundle\Tests\Support\Behaviours\BootKernel
 */
trait BootKernel
{

    /**
     * @var ContainerInterface
     */
    protected $dic;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->dic = static::$kernel->getContainer();

        if (method_exists($this, 'setUpTests')) {
            $this->setUpTests();
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        if (method_exists($this, 'tearDownTests')) {
            $this->tearDownTests();
        }

        $this->dic = null;

        parent::tearDown();
    }
}
