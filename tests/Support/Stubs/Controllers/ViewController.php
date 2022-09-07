<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\Bundles\ApiBundle\Controllers\ApiController;
use Somnambulist\Components\Models\Types\Identity\ExternalIdentity;
use Somnambulist\Components\Models\Types\Identity\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;

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
