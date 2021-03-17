<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities;

use Somnambulist\Components\Domain\Entities\AbstractValueObject;

/**
 * Class ValueObjectWithNulls
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\ValueObjectWithNulls
 */
class ValueObjectWithNulls extends AbstractValueObject
{

    private string $name;
    private ?string $email;
    private ?string $phone;

    public function __construct(string $name, ?string $email, ?string $phone)
    {
        $this->name  = $name;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function toString(): string
    {
        return $this->name;
    }
}
