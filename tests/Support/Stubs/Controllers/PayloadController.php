<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\ApiBundle\Controllers\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PayloadController
 *
 * @package    Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers
 * @subpackage Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers\PayloadController
 */
class PayloadController extends ApiController
{

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function testJsonFilterAction(Request $request)
    {
        return new JsonResponse($request->request->all());
    }
}
