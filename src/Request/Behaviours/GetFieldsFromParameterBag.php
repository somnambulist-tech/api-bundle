<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Symfony\Component\HttpFoundation\ParameterBag;
use function array_filter;
use function explode;

/**
 * Trait GetFieldsFromParameterBag
 *
 * @package    Somnambulist\Bundles\ApiBundle\Request\Behaviours
 * @subpackage Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetFieldsFromParameterBag
 */
trait GetFieldsFromParameterBag
{
    protected function doGetFields(ParameterBag $bag): array
    {
        return array_filter($bag->get('fields', ''));
    }
}
