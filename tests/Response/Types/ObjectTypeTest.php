<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Types;

use League\Fractal\Resource\Item;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Response\Types\ObjectType;

class ObjectTypeTest extends TestCase
{

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testCreate()
    {
        $obj = new ObjectType(
            new \stdClass(),
            static::class,
            key: 'bob',
            includes: ['foo', 'bar'],
            meta: $meta = ['meta' => 'bob']
        );

        $this->assertIsArray($obj->includes());
        $this->assertIsArray($obj->meta());

        $this->assertEquals(static::class, $obj->transformer());
        $this->assertEquals('bob', $obj->key());
        $this->assertEquals(['foo', 'bar'], $obj->includes());
        $this->assertEquals($meta, $obj->meta());
        $this->assertInstanceOf(Item::class, $obj->asResource());
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testFields()
    {
        $obj = new ObjectType(
            $res = new \stdClass(),
            static::class,
            key: 'std_class',
            fields: $a = ['std_class' => 'id,name,type'],
        );

        $this->assertSame($a, $obj->fields());
    }
}
