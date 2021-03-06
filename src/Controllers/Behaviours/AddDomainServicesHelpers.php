<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Controllers\Behaviours;

use Somnambulist\Components\Domain\Commands\CommandBus;
use Somnambulist\Components\Domain\Jobs\JobQueue;
use Somnambulist\Components\Domain\Queries\QueryBus;
use function array_merge;

/**
 * Trait AddDomainServicesHelpers
 *
 * @package    Somnambulist\Bundles\ApiBundle\Controllers\Behaviours
 * @subpackage Somnambulist\Bundles\ApiBundle\Controllers\Behaviours\AddDomainServicesHelpers
 */
trait AddDomainServicesHelpers
{

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            CommandBus::class,
            JobQueue::class,
            QueryBus::class,
        ]);
    }

    protected function query(): QueryBus
    {
        return $this->get(QueryBus::class);
    }

    protected function command(): CommandBus
    {
        return $this->get(CommandBus::class);
    }

    protected function job(): JobQueue
    {
        return $this->get(JobQueue::class);
    }
}
