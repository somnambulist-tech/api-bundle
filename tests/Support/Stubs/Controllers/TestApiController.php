<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers;

use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Somnambulist\ApiBundle\Controllers\ApiController;
use Somnambulist\ApiBundle\Response\Types\CollectionType;
use Somnambulist\ApiBundle\Response\Types\ObjectType;
use Somnambulist\ApiBundle\Response\Types\PagerfantaType;
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
    public function created(ObjectType $type): JsonResponse
    {
        return parent::created($type);
    }

    public function updated(ObjectType $type): JsonResponse
    {
        return parent::updated($type);
    }

    public function deleted($identifier, string $message = 'Record with identifier "%s" deleted successfully'): JsonResponse
    {
        return parent::deleted($identifier, $message);
    }

    public function noContent(): JsonResponse
    {
        return parent::noContent();
    }

    public function returnItemResponse()
    {
        $type = new ObjectType(new MyEntity(1, 'test', 'test 2', DateTime::now()), MyEntityTransformer::class);

        return $this->item($type);
    }

    public function returnCollectionResponse()
    {
        $type = new CollectionType(
            new MutableCollection([
                new MyEntity(1, 'test', 'test 2', DateTime::now()),
                new MyEntity(2, 'test 22', 'test 2', DateTime::now()),
                new MyEntity(3, 'test 33', 'test 3', DateTime::now()),
                new MyEntity(4, 'test 44', 'test 4', DateTime::now()),
            ]),
            MyEntityTransformer::class
        );

        return $this->collection($type);
    }

    public function returnPaginatedResponse()
    {
        $type = new PagerfantaType(
            new Pagerfanta(
                new FixedAdapter(12, [
                    new MyEntity(1, 'test', 'test 2', DateTime::now()),
                    new MyEntity(2, 'test 22', 'test 2', DateTime::now()),
                    new MyEntity(3, 'test 33', 'test 3', DateTime::now()),
                    new MyEntity(4, 'test 44', 'test 4', DateTime::now()),
                ])
            ),
            MyEntityTransformer::class,
            'http://www.example.org/path/to/resource?arg=1&this=that'
        );

        return $this->paginate($type);
    }
}
