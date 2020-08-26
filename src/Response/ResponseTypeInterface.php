<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Response;

use League\Fractal\Resource\ResourceAbstract;

/**
 * Interface ResponseTypeInterface
 *
 * @package    Somnambulist\ApiBundle\Response
 * @subpackage Somnambulist\ApiBundle\Response\ResponseTypeInterface
 */
interface ResponseTypeInterface
{
    public function asResource(): ResourceAbstract;

    public function getIncludes(): array;

    public function getMeta(): array;
}
