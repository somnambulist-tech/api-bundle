<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Types;

use League\Fractal\Resource\Collection;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Response\Types\PagerfantaType;

/**
 * Class PagerfantaTypeTest
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Transformer
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Transformer\PagerfantaTypeTest
 */
class PagerfantaTypeTest extends TestCase
{

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testCreatePaginator()
    {
        $obj = new PagerfantaType(new Pagerfanta(new ArrayAdapter([])), static::class, 'http://www.example.com');

        $this->assertIsArray($obj->getIncludes());
        $this->assertIsArray($obj->getMeta());

        $obj->withKey('bob')->withIncludes('foo', 'bar')->withMeta($meta = ['meta' => 'bob']);

        $this->assertEquals(static::class, $obj->getTransformer());
        $this->assertEquals('bob', $obj->getKey());
        $this->assertEquals('http://www.example.com', $obj->getUrl());
        $this->assertEquals(['foo', 'bar'], $obj->getIncludes());
        $this->assertEquals($meta, $obj->getMeta());
        $this->assertInstanceOf(Collection::class, $obj->asResource());
    }
}
