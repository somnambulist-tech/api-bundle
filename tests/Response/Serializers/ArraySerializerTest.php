<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Serializers;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use Somnambulist\Bundles\ApiBundle\Response\Serializers\ArraySerializer;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyEntity;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\MyEntityTransformer;
use Somnambulist\Components\Collection\MutableCollection as Collection;
use Somnambulist\Components\Models\Types\DateTime\DateTime;
use Somnambulist\Components\Utils\EntityAccessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArraySerializerTest extends KernelTestCase
{
    use BootKernel;

    /**
     * @group services
     * @group services-response
     * @group services-response-serializer
     */
    public function testCollectionsMaintainASingleDataKey()
    {
        $manager = $this->dic->get(Manager::class);
        $entity  = new MyEntity(123, 'test', 'another', DateTime::now());

        EntityAccessor::set($entity, 'id', 1, $entity);

        $collection = new Collection([$entity]);

        $resource = new FractalCollection($collection, new MyEntityTransformer(), 'data');

        $serialized = $manager
            ->setSerializer(new ArraySerializer())
            ->createData($resource)
            ->toArray()
        ;

        $this->assertArrayHasKey('data', $serialized);
        $this->assertCount(1, $serialized['data']);
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-serializer
     */
    public function testSerializeCollectionsWithoutKey()
    {
        $manager = $this->dic->get(Manager::class);
        $entity  = new MyEntity(123, 'test', 'another', DateTime::now());

        EntityAccessor::set($entity, 'id', 1, $entity);

        $collection = new Collection([$entity]);

        $resource = new FractalCollection($collection, new MyEntityTransformer());

        $serialized = $manager
            ->setSerializer(new ArraySerializer())
            ->createData($resource)
            ->toArray()
        ;

        $this->assertArrayNotHasKey('data', $serialized);
        $this->assertCount(3, $serialized[0]);
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-serializer
     */
    public function testItemCanGetADataKey()
    {
        $manager = $this->dic->get(Manager::class);
        $entity  = new MyEntity(123, 'test', 'another', DateTime::now());

        EntityAccessor::set($entity, 'id', 1, $entity);

        $resource = new FractalItem($entity, new MyEntityTransformer(), 'data');

        $serialized = $manager
            ->setSerializer(new ArraySerializer())
            ->createData($resource)
            ->toArray()
        ;

        $this->assertArrayHasKey('data', $serialized);
        $this->assertCount(1, $serialized);
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-serializer
     */
    public function testSerializeWithoutKey()
    {
        $manager = $this->dic->get(Manager::class);
        $entity  = new MyEntity(123, 'test', 'another', DateTime::now());

        EntityAccessor::set($entity, 'id', 1, $entity);

        $resource = new FractalItem($entity, new MyEntityTransformer());

        $serialized = $manager
            ->setSerializer(new ArraySerializer())
            ->createData($resource)
            ->toArray()
        ;

        $this->assertArrayNotHasKey('data', $serialized);
        $this->assertCount(3, $serialized);
    }
}
