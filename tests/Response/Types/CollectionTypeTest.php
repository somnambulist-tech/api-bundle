<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Response\Types;

use League\Fractal\Resource\Collection as FractalCollection;
use PHPUnit\Framework\TestCase;
use Somnambulist\ApiBundle\Response\Types\CollectionType;
use Somnambulist\Collection\MutableCollection as Collection;

/**
 * Class CollectionTypeTest
 *
 * @package    Somnambulist\ApiBundle\Tests\Response\Types
 * @subpackage Somnambulist\ApiBundle\Tests\Response\Types\CollectionTypeTest
 */
class CollectionTypeTest extends TestCase
{

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testCreateCollection()
    {
        $obj = new CollectionType(new Collection(new \stdClass()), static::class);

        $this->assertIsArray($obj->getIncludes());
        $this->assertIsArray($obj->getMeta());

        $obj->withKey('bob')->withIncludes('foo', 'bar')->withMeta($meta = ['meta' => 'bob']);

        $this->assertEquals(static::class, $obj->getTransformer());
        $this->assertEquals('bob', $obj->getKey());
        $this->assertEquals(['foo', 'bar'], $obj->getIncludes());
        $this->assertEquals($meta, $obj->getMeta());
        $this->assertInstanceOf(FractalCollection::class, $obj->asResource());
    }
}
