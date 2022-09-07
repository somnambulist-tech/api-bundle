<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers;

use Assert\Assert;
use Exception;
use Somnambulist\Bundles\ApiBundle\Controllers\ApiController;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyAssertingEntity;
use Somnambulist\Components\Models\Exceptions\EntityNotFoundException;
use Somnambulist\Components\Models\Exceptions\InvalidDomainStateException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class ExceptionController extends ApiController
{

    public function notFoundAction()
    {
        throw EntityNotFoundException::entityNotFound(__CLASS__, 'test');
    }

    public function invalidDomainStateAction()
    {
        throw new InvalidDomainStateException();
    }

    public function previousAction()
    {
        throw new InvalidDomainStateException('Invalid state', 422, EntityNotFoundException::entityNotFound(__CLASS__, 'test'));
    }

    public function assertAction()
    {
        Assert::that('', null, 'property_path')->minLength(10)->maxLength(200)->notNull();
    }

    public function assertLazyAction()
    {
        Assert::lazy()
            ->that('', 'property_path')->minLength(10)->maxLength(200)->notNull()
            ->that('bar', 'property_path_2')->uuid()->notNull()
            ->verifyNow()
        ;
    }

    public function assertLazyTryAllAction()
    {
        new MyAssertingEntity('', '', '', '');
    }

    public function messengerAction()
    {
        try {
            new MyAssertingEntity('', '', '', '');
        } catch (Exception $e) {
            throw new HandlerFailedException(new Envelope(new stdClass()), [$e]);
        }
    }
}
