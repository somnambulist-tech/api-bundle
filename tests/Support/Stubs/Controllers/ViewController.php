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

    /**
     * @param Uuid $id
     *
     * @return JsonResponse
     */
    public function uuidAction(Uuid $id)
    {
        return new JsonResponse([
            'value' => (string)$id,
        ]);
    }

    /**
     * @param Uuid $id
     * @param Uuid $second
     * @param Uuid $third
     *
     * @return JsonResponse
     */
    public function multiUuidAction(Uuid $id, Uuid $second, Uuid $third)
    {
        return new JsonResponse([
            'value1' => (string)$id,
            'value2' => (string)$second,
            'value3' => (string)$third,
        ]);
    }

    /**
     * @param ExternalIdentity $id
     *
     * @return JsonResponse
     */
    public function externalIdAction(ExternalIdentity $id)
    {
        return new JsonResponse([
            'provider' => (string)$id->provider(),
            'identity' => (string)$id->identity(),
        ]);
    }
}
