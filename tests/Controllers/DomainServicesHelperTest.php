<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Controllers;

use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers\TestApiController;
use Somnambulist\Components\Domain\Commands\CommandBus;
use Somnambulist\Components\Domain\Jobs\JobQueue;
use Somnambulist\Components\Domain\Queries\QueryBus;
use Somnambulist\Components\Domain\Utils\EntityAccessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DomainServicesHelperTest extends KernelTestCase
{

    use BootKernel;

    public function testServicesAreRegistered()
    {
        /** @var TestApiController $controller */
        $controller = static::getContainer()->get(TestApiController::class);

        $this->assertTrue(EntityAccessor::call($controller, 'has', $controller, CommandBus::class));
        $this->assertTrue(EntityAccessor::call($controller, 'has', $controller, JobQueue::class));
        $this->assertTrue(EntityAccessor::call($controller, 'has', $controller, QueryBus::class));
    }
}
