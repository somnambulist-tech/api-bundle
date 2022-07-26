<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\Bundles\ApiBundle\Controllers\ApiController;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\CreateUserFormRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateUserController extends ApiController
{

    public function __invoke(CreateUserFormRequest $form): JsonResponse
    {
        return new JsonResponse($form->request->all());
    }
}
