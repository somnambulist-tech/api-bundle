<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\ApiBundle\Controllers\ApiController;
use Somnambulist\Domain\Entities\Types\Identity\ExternalIdentity;
use Somnambulist\Domain\Entities\Types\Identity\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use function json_encode;

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
