<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\Bundles\ApiBundle\Controllers\ApiController;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\UpdateUserFromRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UpdateUserController
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers\UpdateUserController
 */
class UpdateUserController extends ApiController
{

    public function __invoke(UpdateUserFromRequest $form): JsonResponse
    {
        return new JsonResponse($form->request->all());
    }
}
