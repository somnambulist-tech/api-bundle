<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities;

use Somnambulist\Components\Models\AbstractMultiton;

/**
 * @method static MyMultitonEnum ONE()
 * @method static MyMultitonEnum TWO()
 * @method static MyMultitonEnum THREE()
 */
class MyMultitonEnum extends AbstractMultiton
{
    public const ONE   = 1;
    public const TWO   = 2;
    public const THREE = 3;

    protected static function initializeMembers()
    {

    }

    public function toString(): string
    {
        return $this->key();
    }

    public function equals(object $object): bool
    {
        return $object::class === $this::class && $this->key() === $object->key();
    }
}
