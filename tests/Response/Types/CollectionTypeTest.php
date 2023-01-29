<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Types;

use League\Fractal\Resource\Collection as FractalCollection;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Response\Types\CollectionType;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\SearchFormRequest;
use Somnambulist\Components\Collection\MutableCollection as Collection;
use Symfony\Component\HttpFoundation\Request;

class CollectionTypeTest extends TestCase
{
    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testCreateCollection()
    {
        $obj = new CollectionType(
            new Collection(new \stdClass()),
            static::class,
            key: 'bob',
            includes: ['foo', 'bar'],
            meta: $meta = ['meta' => 'bob'],
        );

        $this->assertIsArray($obj->includes());
        $this->assertIsArray($obj->meta());

        $this->assertEquals(static::class, $obj->transformer());
        $this->assertEquals('bob', $obj->key());
        $this->assertEquals(['foo', 'bar'], $obj->includes());
        $this->assertEquals($meta, $obj->meta());
        $this->assertInstanceOf(FractalCollection::class, $obj->asResource());
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testCreateFromFormRequest()
    {
        $obj = CollectionType::fromFormRequest(
            new SearchFormRequest(Request::createFromGlobals()),
            new Collection(new \stdClass()),
            static::class,
        );

        $this->assertIsArray($obj->includes());
        $this->assertIsArray($obj->meta());
        $this->assertEquals(static::class, $obj->transformer());
        $this->assertEquals('data', $obj->key());
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testCreateCollectionWithoutKey()
    {
        $obj = new CollectionType(
            new Collection(new \stdClass()),
            static::class,
            key: null,
            includes: ['foo', 'bar'],
            meta: $meta = ['meta' => 'bob'],
        );

        $this->assertIsArray($obj->includes());
        $this->assertIsArray($obj->meta());

        $this->assertEquals(static::class, $obj->transformer());
        $this->assertNull($obj->key());
        $this->assertEquals(['foo', 'bar'], $obj->includes());
        $this->assertEquals($meta, $obj->meta());
        $this->assertInstanceOf(FractalCollection::class, $obj->asResource());
    }
}
