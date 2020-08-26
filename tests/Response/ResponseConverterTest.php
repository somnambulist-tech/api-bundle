<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Response;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Somnambulist\ApiBundle\Response\ResponseConverter;
use Somnambulist\ApiBundle\Response\Types\PagerfantaType;
use Somnambulist\ApiBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\ApiBundle\Tests\Support\Stubs\Entities\MyEntity;
use Somnambulist\ApiBundle\Tests\Support\Stubs\MyEntityTransformer;
use Somnambulist\Domain\Entities\Types\DateTime\DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ResponseConverterTest
 *
 * @package    Somnambulist\ApiBundle\Tests\Response
 * @subpackage Somnambulist\ApiBundle\Tests\Response\ResponseConverterTest
 */
class ResponseConverterTest extends KernelTestCase
{

    use BootKernel;

    /**
     * @group services
     * @group services-response
     * @group services-response-converter
     */
    public function testPaginator()
    {
        $transformer = new PagerfantaType(
            (new Pagerfanta(
                new ArrayAdapter([
                    new MyEntity(1, 'test', 'test 1', DateTime::now()),
                    new MyEntity(2, 'test', 'test 2', DateTime::now()),
                    new MyEntity(3, 'test', 'test 3', DateTime::now()),
                    new MyEntity(4, 'test', 'test 4', DateTime::now()),
                    new MyEntity(5, 'test', 'test 5', DateTime::now()),
                ])
            ))->setMaxPerPage(1)->setCurrentPage(2),
            MyEntityTransformer::class,
            'http://example.com/foo/bar?foo=bar'
        );

        $converter = $this->dic->get(ResponseConverter::class);
        $response  = $converter->toJson($transformer);
        $data      = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertArrayHasKey('next', $data['meta']['pagination']['links']);
        $this->assertStringContainsString('page=3', $data['meta']['pagination']['links']['next']);
        $this->assertArrayHasKey('previous', $data['meta']['pagination']['links']);
        $this->assertStringContainsString('page=1', $data['meta']['pagination']['links']['previous']);
    }

    /**
     * @group services
     * @group services-response
     * @group services-response-converter
     */
    public function testPaginatorWithoutQueryOrPath()
    {
        $transformer = new PagerfantaType(
            (new Pagerfanta(
                new ArrayAdapter([
                    new MyEntity(1, 'test', 'test 1', DateTime::now()),
                    new MyEntity(2, 'test', 'test 2', DateTime::now()),
                    new MyEntity(3, 'test', 'test 3', DateTime::now()),
                    new MyEntity(4, 'test', 'test 4', DateTime::now()),
                    new MyEntity(5, 'test', 'test 5', DateTime::now()),
                ])
            ))->setMaxPerPage(1)->setCurrentPage(2),
            MyEntityTransformer::class,
            'http://example.com'
        );

        $converter = $this->dic->get(ResponseConverter::class);
        $response  = $converter->toJson($transformer);
        $data      = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertArrayHasKey('next', $data['meta']['pagination']['links']);
        $this->assertStringContainsString('page=3', $data['meta']['pagination']['links']['next']);
        $this->assertArrayHasKey('previous', $data['meta']['pagination']['links']);
        $this->assertStringContainsString('page=1', $data['meta']['pagination']['links']['previous']);
    }
}
