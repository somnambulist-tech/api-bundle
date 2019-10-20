<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers;

use Assert\InvalidArgumentException;
use Somnambulist\ApiBundle\Controllers\ApiController;
use Somnambulist\ApiBundle\Tests\Support\Stubs\Entities\MyAssertingEntity;
use Somnambulist\Domain\Entities\Exceptions\EntityNotFoundException;
use Somnambulist\Domain\Entities\Exceptions\InvalidDomainStateException;

/**
 * Class ExceptionController
 *
 * @package    Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers
 * @subpackage Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers\ExceptionController
 */
class ExceptionController extends ApiController
{

    /**
     * @throws EntityNotFoundException
     */
    public function notFoundAction()
    {
        throw EntityNotFoundException::entityNotFound(__CLASS__, 'test');
    }

    /**
     * @throws InvalidDomainStateException
     */
    public function invalidDomainStateAction()
    {
        throw new InvalidDomainStateException();
    }

    /**
     * @throws InvalidDomainStateException
     */
    public function previousAction()
    {
        throw new InvalidDomainStateException('Invalid state', 422, EntityNotFoundException::entityNotFound(__CLASS__, 'test'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function assertAction()
    {
        new MyAssertingEntity('', '', '', '');
    }
}
