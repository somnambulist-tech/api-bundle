<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Types;

use League\Fractal\Resource\Collection;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Response\Types\PagerfantaType;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\SearchFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
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
        $obj = new PagerfantaType(
            new Pagerfanta(new ArrayAdapter([])),
            static::class,
            'http://www.example.com',
            key: 'bob',
            includes: ['foo', 'bar'],
            meta: $meta = ['meta' => 'bob']
        );

        $this->assertIsArray($obj->includes());
        $this->assertIsArray($obj->meta());

        $this->assertEquals(static::class, $obj->transformer());
        $this->assertEquals('bob', $obj->key());
        $this->assertEquals('http://www.example.com', $obj->url());
        $this->assertEquals(['foo', 'bar'], $obj->includes());
        $this->assertEquals($meta, $obj->meta());
        $this->assertInstanceOf(Collection::class, $obj->asResource());
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-type
     */
    public function testFromFormRequest()
    {
        $request = new SearchFormRequest(
            Request::create(
                'http://www.example.com/?include=foo,bar&page=4&per_page=30', 'GET',
                $q = [
                    'include'  => 'foo,bar',
                    'page'     => 4,
                    'per_page' => 30,
                ],
            )
        );
        FormRequest::appendValidationData($request, $q);

        $obj = PagerfantaType::fromFormRequest(
            $request, new Pagerfanta(new ArrayAdapter([])),
            static::class,
            key: 'bob',
            meta: $meta = ['meta' => 'bob'],
        );

        $this->assertIsArray($obj->includes());
        $this->assertIsArray($obj->meta());

        $this->assertEquals(static::class, $obj->transformer());
        $this->assertEquals('bob', $obj->key());
        $this->assertStringContainsString('http://www.example.com', $obj->url());
        $this->assertEquals(['foo', 'bar'], $obj->includes());
        $this->assertEquals($meta, $obj->meta());
        $this->assertInstanceOf(Collection::class, $obj->asResource());
    }
}
