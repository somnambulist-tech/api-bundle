<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Services\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Somnambulist\ApiBundle\Services\Transformer\TransformerBinding;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Somnambulist\Collection\MutableCollection as Collection;

/**
 * Class TransformerBindingTest
 *
 * @package    Somnambulist\ApiBundle\Tests\Services\Transformer
 * @subpackage Somnambulist\ApiBundle\Tests\Services\Transformer\TransformerBindingTest
 */
class TransformerBindingTest extends TestCase
{

    /**
     * @group services
     * @group services-transformer
     * @group services-transformer-binding
     */
    public function testCreate()
    {
        $obj = TransformerBinding::item($res = new \stdClass(), static::class);

        $this->assertIsArray($obj->getIncludes());
        $this->assertIsArray($obj->getMeta());
        $this->assertNull($obj->getKey());

        $obj
            ->withKey('bob')
            ->withIncludes($inc = ['foo', 'bar'])
            ->withMeta($meta = ['meta' => 'bob'])
            ->withUrl('//example.com/foo/bar')
        ;

        $this->assertEquals(static::class, $obj->getTransformer());
        $this->assertEquals('bob', $obj->getKey());
        $this->assertEquals($inc, $obj->getIncludes());
        $this->assertEquals($meta, $obj->getMeta());
        $this->assertEquals('item', $obj->getType());
        $this->assertEquals('//example.com/foo/bar', $obj->getUrl());
        $this->assertEquals($res, $obj->getResource());
    }

    /**
     * @group services
     * @group services-transformer
     * @group services-transformer-binding
     */
    public function testCreateCollection()
    {
        $obj = TransformerBinding::collection(new Collection(new \stdClass()), static::class);

        $this->assertIsArray($obj->getIncludes());
        $this->assertIsArray($obj->getMeta());

        $obj->withKey('bob')->withIncludes($inc = ['foo', 'bar'])->withMeta($meta = ['meta' => 'bob']);

        $this->assertEquals(static::class, $obj->getTransformer());
        $this->assertEquals('bob', $obj->getKey());
        $this->assertEquals($inc, $obj->getIncludes());
        $this->assertEquals($meta, $obj->getMeta());
        $this->assertEquals('collection', $obj->getType());
    }

    /**
     * @group services
     * @group services-transformer
     * @group services-transformer-binding
     */
    public function testCreatePaginator()
    {
        $obj = TransformerBinding::paginate(new Pagerfanta(new ArrayAdapter([])), static::class);

        $this->assertIsArray($obj->getIncludes());
        $this->assertIsArray($obj->getMeta());

        $obj->withKey('bob')->withIncludes($inc = ['foo', 'bar'])->withMeta($meta = ['meta' => 'bob']);

        $this->assertEquals(static::class, $obj->getTransformer());
        $this->assertEquals('bob', $obj->getKey());
        $this->assertEquals($inc, $obj->getIncludes());
        $this->assertEquals($meta, $obj->getMeta());
        $this->assertEquals('collection', $obj->getType());
    }

    /**
     * @group services
     * @group services-transformer
     * @group services-transformer-binding
     * @dataProvider collectionInstances
     */
    public function testCollectionsAndPaginatorsIdentifiedAsCollections($collection)
    {
        $obj = TransformerBinding::item($collection, static::class);

        $this->assertEquals('collection', $obj->getType());
    }

    public function collectionInstances()
    {
        return [
            [new Collection(),],
            [new ArrayCollection(),],
            [new Pagerfanta(new ArrayAdapter([]))],
        ];
    }
}
