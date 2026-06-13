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
    public const int ONE   = 1;
    public const int TWO   = 2;
    public const int THREE = 3;

    protected static function initializeMembers(): void
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
