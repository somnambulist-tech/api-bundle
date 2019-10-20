<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Services\Transformer\Serializers;

use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use Somnambulist\ApiBundle\Services\Transformer\Serializers\ArraySerializer;
use Somnambulist\ApiBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\ApiBundle\Tests\Support\Stubs\Entities\MyEntity;
use Somnambulist\ApiBundle\Tests\Support\Stubs\MyEntityTransformer;
use Somnambulist\Collection\MutableCollection as Collection;
use Somnambulist\Domain\Entities\Types\DateTime\DateTime;
use Somnambulist\Domain\Utils\EntityAccessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ArraySerializerTest
 *
 * @package Somnambulist\ApiBundle\Tests\Services\Transformer\Serializers
 * @subpackage Somnambulist\ApiBundle\Tests\Services\Transformer\Serializers\ArraySerializerTest
 */
class ArraySerializerTest extends KernelTestCase
{

    use BootKernel;

    /**
     * @group services
     * @group services-transformer
     * @group services-transformer-serializer
     */
    public function testCollectionsMaintainASingleDataKey()
    {
        $manager = $this->dic->get('sam_j_fractal.manager');
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
     * @group services-transformer
     * @group services-transformer-serializer
     */
    public function testSerializeCollectionsWithoutKey()
    {
        $manager = $this->dic->get('sam_j_fractal.manager');
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
     * @group services-transformer
     * @group services-transformer-serializer
     */
    public function testItemCanGetADataKey()
    {
        $manager = $this->dic->get('sam_j_fractal.manager');
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
     * @group services-transformer
     * @group services-transformer-serializer
     */
    public function testSerializeWithoutKey()
    {
        $manager = $this->dic->get('sam_j_fractal.manager');
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
