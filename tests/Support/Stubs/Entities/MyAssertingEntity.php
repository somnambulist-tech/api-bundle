<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities;

use Assert\Assert;
use DateTimeInterface;

/**
 * Class MyAssertingEntity
 *
 * @package Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyAssertingEntity
 */
class MyAssertingEntity
{

    protected $id;

    protected $name;

    protected $another;

    protected $createdAt;

    protected $related;

    /**
     * Constructor.
     *
     * @param $id
     * @param $name
     * @param $another
     * @param $createdAt
     */
    public function __construct($id, $name, $another, $createdAt)
    {
        Assert::lazy()->tryAll()
            ->that($id, 'id')->notEmpty()->maxLength(100)
            ->that($name, 'name')->notEmpty()->maxLength(100)
            ->that($another, 'another')->notEmpty()->maxLength(100)
            ->that($createdAt, 'createdAt')->isInstanceOf(DateTimeInterface::class)
            ->verifyNow()
        ;

        $this->id        = $id;
        $this->name      = $name;
        $this->another   = $another;
        $this->createdAt = $createdAt;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAnother()
    {
        return $this->another;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
