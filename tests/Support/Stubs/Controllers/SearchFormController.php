<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\Bundles\ApiBundle\Controllers\ApiController;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\SearchFormRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SearchFormController
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Controllers\SearchFormController
 */
class SearchFormController extends ApiController
{

    public function __invoke(SearchFormRequest $form): JsonResponse
    {
        return new JsonResponse($form->query->all());
    }
}
