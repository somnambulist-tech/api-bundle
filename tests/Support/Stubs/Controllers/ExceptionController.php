<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers;

use Assert\Assert;
use Assert\InvalidArgumentException;
use Exception;
use http\Env;
use RuntimeException;
use Somnambulist\ApiBundle\Controllers\ApiController;
use Somnambulist\ApiBundle\Tests\Support\Stubs\Entities\MyAssertingEntity;
use Somnambulist\Domain\Entities\Exceptions\EntityNotFoundException;
use Somnambulist\Domain\Entities\Exceptions\InvalidDomainStateException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

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
        Assert::that('', null, 'property_path')->minLength(10)->maxLength(200)->notNull();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function assertLazyAction()
    {
        Assert::lazy()
            ->that('', 'property_path')->minLength(10)->maxLength(200)->notNull()
            ->that('bar', 'property_path_2')->uuid()->notNull()
            ->verifyNow()
        ;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function assertLazyTryAllAction()
    {
        new MyAssertingEntity('', '', '', '');
    }

    /**
     * @throws HandlerFailedException
     */
    public function messengerAction()
    {
        try {
            new MyAssertingEntity('', '', '', '');
        } catch (Exception $e) {
            throw new HandlerFailedException(new Envelope(new stdClass()), [$e]);
        }
    }
}
