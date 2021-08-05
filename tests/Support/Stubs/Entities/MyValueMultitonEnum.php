<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities;

use Somnambulist\Components\Domain\Entities\AbstractEnumeration;

/**
 * Class MyValueMultitonEnum
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyValueMultitonEnum
 *
 * @method static MyValueMultitonEnum ONE()
 * @method static MyValueMultitonEnum TWO()
 * @method static MyValueMultitonEnum THREE()
 */
class MyValueMultitonEnum extends AbstractEnumeration
{
    public const ONE   = 'one';
    public const TWO   = 'two';
    public const THREE = 'three';
}
