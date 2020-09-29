<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities;

/**
 * Class MyEntity
 *
 * @package Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyEntity
 */
class MyEntity
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
