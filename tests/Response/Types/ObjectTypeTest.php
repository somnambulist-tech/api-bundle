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
        $obj = new ObjectType(new \stdClass(), static::class);

        $this->assertIsArray($obj->getIncludes());
        $this->assertIsArray($obj->getMeta());
        $this->assertNull($obj->getKey());

        $obj
            ->key('bob')
            ->include('foo', 'bar')
            ->meta($meta = ['meta' => 'bob'])
        ;

        $this->assertEquals(static::class, $obj->getTransformer());
        $this->assertEquals('bob', $obj->getKey());
        $this->assertEquals(['foo', 'bar'], $obj->getIncludes());
        $this->assertEquals($meta, $obj->getMeta());
        $this->assertInstanceOf(Item::class, $obj->asResource());
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testFields()
    {
        $obj = new ObjectType($res = new \stdClass(), static::class, key: 'std_class');
        $obj->fields($a = ['std_class' => 'id,name,type']);

        $this->assertSame($a, $obj->getFields());
    }
}
