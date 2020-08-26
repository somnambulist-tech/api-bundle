<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Response\Types;

use League\Fractal\Resource\Item;
use PHPUnit\Framework\TestCase;
use Somnambulist\ApiBundle\Response\Types\ObjectType;

/**
 * Class ObjectTypeTest
 *
 * @package    Somnambulist\ApiBundle\Tests\Response\Types
 * @subpackage Somnambulist\ApiBundle\Tests\Response\Types\ObjectTypeTest
 */
class ObjectTypeTest extends TestCase
{

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testCreate()
    {
        $obj = new ObjectType($res = new \stdClass(), static::class);

        $this->assertIsArray($obj->getIncludes());
        $this->assertIsArray($obj->getMeta());
        $this->assertNull($obj->getKey());

        $obj
            ->withKey('bob')
            ->withIncludes('foo', 'bar')
            ->withMeta($meta = ['meta' => 'bob'])
        ;

        $this->assertEquals(static::class, $obj->getTransformer());
        $this->assertEquals('bob', $obj->getKey());
        $this->assertEquals(['foo', 'bar'], $obj->getIncludes());
        $this->assertEquals($meta, $obj->getMeta());
        $this->assertInstanceOf(Item::class, $obj->asResource());
    }
}
