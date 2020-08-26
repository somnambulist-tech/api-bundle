<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\ApiBundle\Controllers\ApiController;
use Somnambulist\Domain\Entities\Types\Identity\ExternalIdentity;
use Somnambulist\Domain\Entities\Types\Identity\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ViewController
 *
 * @package Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers
 * @subpackage Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers\ViewController
 */
class ViewController extends ApiController
{

    public function uuidAction(Uuid $id): JsonResponse
    {
        return new JsonResponse([
            'value' => (string)$id,
        ]);
    }

    public function multiUuidAction(Uuid $id, Uuid $second, Uuid $third): JsonResponse
    {
        return new JsonResponse([
            'value1' => (string)$id,
            'value2' => (string)$second,
            'value3' => (string)$third,
        ]);
    }

    public function externalIdAction(ExternalIdentity $id): JsonResponse
    {
        return new JsonResponse([
            'provider' => (string)$id->provider(),
            'identity' => (string)$id->identity(),
        ]);
    }
}
