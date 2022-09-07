<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Controllers\Behaviours;

use Somnambulist\Components\Commands\CommandBus;
use Somnambulist\Components\Jobs\JobQueue;
use Somnambulist\Components\Queries\QueryBus;

use function array_merge;

/**
 * Adds helper methods / services for loading the somnambulist/domain buses.
 */
trait AddDomainServicesHelpers
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            CommandBus::class,
            JobQueue::class,
            QueryBus::class,
        ]);
    }

    protected function query(): QueryBus
    {
        return $this->container->get(QueryBus::class);
    }

    protected function command(): CommandBus
    {
        return $this->container->get(CommandBus::class);
    }

    protected function job(): JobQueue
    {
        return $this->container->get(JobQueue::class);
    }
}
