<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities;

use Somnambulist\Components\Domain\Entities\AbstractEnumeration;

/**
 * Class MyEnum
 *
 * @package Support\Stubs\Entities
 *
 * @method static MyEnum ONE()
 * @method static MyEnum TWO()
 * @method static MyEnum THREE()
 */
class MyEnum extends AbstractEnumeration
{
    public const ONE   = 'one';
    public const TWO   = 'two';
    public const THREE = 'three';
}
