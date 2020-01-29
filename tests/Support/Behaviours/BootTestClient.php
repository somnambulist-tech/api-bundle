<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Behaviours;

use function method_exists;

/**
 * Trait BootTestClient
 *
 * @package    Somnambulist\ApiBundle\Tests\Support\Behaviours
 * @subpackage Somnambulist\ApiBundle\Tests\Support\Behaviours\BootTestClient
 *
 * @method void setKernelClass()
 * @method void setUpTests()
 */
trait BootTestClient
{

    protected $client;

    /**
     * {@inheritDoc}
     */
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
