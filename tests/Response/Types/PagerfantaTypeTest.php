<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Types;

use League\Fractal\Resource\Collection;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;
use Somnambulist\Bundles\ApiBundle\Response\Types\PagerfantaType;
use Symfony\Component\HttpFoundation\Request;

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

        $obj->key('bob')->include('foo', 'bar')->meta($meta = ['meta' => 'bob']);

        $this->assertEquals(static::class, $obj->getTransformer());
        $this->assertEquals('bob', $obj->getKey());
        $this->assertEquals('http://www.example.com', $obj->getUrl());
        $this->assertEquals(['foo', 'bar'], $obj->getIncludes());
        $this->assertEquals($meta, $obj->getMeta());
        $this->assertInstanceOf(Collection::class, $obj->asResource());
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testFromFormRequest()
    {
        $request = new FormRequest(
            Request::create(
                'http://www.example.com/?include=foo,bar&page=4&per_page=30', 'GET',
                [
                    'include'  => 'foo,bar',
                    'page'     => 4,
                    'per_page' => 30,
                ],
            )
        );

        $obj = PagerfantaType::fromFormRequest(
            $request, new Pagerfanta(new ArrayAdapter([])), static::class, $meta = ['meta' => 'bob'], 'bob'
        );

        $this->assertIsArray($obj->getIncludes());
        $this->assertIsArray($obj->getMeta());

        $this->assertEquals(static::class, $obj->getTransformer());
        $this->assertEquals('bob', $obj->getKey());
        $this->assertStringContainsString('http://www.example.com', $obj->getUrl());
        $this->assertEquals(['foo', 'bar'], $obj->getIncludes());
        $this->assertEquals($meta, $obj->getMeta());
        $this->assertInstanceOf(Collection::class, $obj->asResource());
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testCanChangeUrl()
    {
        $obj = new PagerfantaType(new Pagerfanta(new ArrayAdapter([])), static::class, 'http://www.example.com');

        $obj->url('https://example.com');

        $this->assertEquals('https://example.com', $obj->getUrl());
    }
}
