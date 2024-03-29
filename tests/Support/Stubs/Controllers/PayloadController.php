<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\Bundles\ApiBundle\Controllers\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PayloadController extends ApiController
{

    public function testJsonFilterAction(Request $request): JsonResponse
    {
        return new JsonResponse($request->request->all());
    }
}
