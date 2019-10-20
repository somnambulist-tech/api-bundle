<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\ApiBundle\Controllers\ApiController;
use Somnambulist\ApiBundle\Services\Transformer\TransformerBinding;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class TestApiController
 *
 * @package    Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers
 * @subpackage Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers\TestApiController
 */
class TestApiController extends ApiController
{
    public function created(TransformerBinding $binding): JsonResponse
    {
        return parent::created($binding);
    }

    public function updated(TransformerBinding $binding): JsonResponse
    {
        return parent::updated($binding);
    }

    public function deleted($identifier): JsonResponse
    {
        return parent::deleted($identifier);
    }

    public function noContent(): JsonResponse
    {
        return parent::noContent();
    }
}
