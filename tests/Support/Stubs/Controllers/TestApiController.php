<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers;

use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Somnambulist\ApiBundle\Controllers\ApiController;
use Somnambulist\ApiBundle\Services\Transformer\TransformerBinding;
use Somnambulist\ApiBundle\Tests\Support\Stubs\Entities\MyEntity;
use Somnambulist\ApiBundle\Tests\Support\Stubs\MyEntityTransformer;
use Somnambulist\Collection\MutableCollection;
use Somnambulist\Domain\Entities\Types\DateTime\DateTime;
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

    public function returnItemResponse()
    {
        $binding = TransformerBinding::item(new MyEntity(1, 'test', 'test 2', DateTime::now()), new MyEntityTransformer());

        return $this->item($binding);
    }

    public function returnCollectionResponse()
    {
        $binding = TransformerBinding::collection(
            new MutableCollection([
                new MyEntity(1, 'test', 'test 2', DateTime::now()),
                new MyEntity(2, 'test 22', 'test 2', DateTime::now()),
                new MyEntity(3, 'test 33', 'test 3', DateTime::now()),
                new MyEntity(4, 'test 44', 'test 4', DateTime::now()),
            ]),
            new MyEntityTransformer()
        );

        return $this->collection($binding);
    }

    public function returnPaginatedResponse()
    {
        $binding = TransformerBinding::paginate(
            new Pagerfanta(
                new FixedAdapter(12, [
                    new MyEntity(1, 'test', 'test 2', DateTime::now()),
                    new MyEntity(2, 'test 22', 'test 2', DateTime::now()),
                    new MyEntity(3, 'test 33', 'test 3', DateTime::now()),
                    new MyEntity(4, 'test 44', 'test 4', DateTime::now()),
                ])
            ),
            new MyEntityTransformer(),
            'http://www.example.org/path/to/resource?arg=1&this=that'
        );

        return $this->collection($binding);
    }
}
