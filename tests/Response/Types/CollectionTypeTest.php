<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Types;

use League\Fractal\Resource\Collection as FractalCollection;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Response\Types\CollectionType;
use Somnambulist\Components\Collection\MutableCollection as Collection;

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
}
