<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Controllers;

use Somnambulist\ApiBundle\Services\Request\RequestArgumentHelper;
use Somnambulist\ApiBundle\Services\Response\ResponseFactory;
use Somnambulist\ApiBundle\Services\Transformer\TransformerBinding;
use Somnambulist\ApiBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\ApiBundle\Tests\Support\Stubs\Controllers\TestApiController;
use Somnambulist\ApiBundle\Tests\Support\Stubs\Entities\MyEntity;
use Somnambulist\ApiBundle\Tests\Support\Stubs\MyEntityTransformer;
use Somnambulist\Domain\Entities\Types\DateTime\DateTime;
use Somnambulist\Domain\Utils\EntityAccessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiControllerTest
 *
 * @package    Somnambulist\ApiBundle\Tests\Controllers
 * @subpackage Somnambulist\ApiBundle\Tests\Controllers\ApiControllerTest
 */
class ApiControllerTest extends KernelTestCase
{

    use BootKernel;

    /**
     * @group controllers
     * @group controllers-api
     */
    public function testResponseFactory()
    {
        $ctrl = new TestApiController();
        $ctrl->setContainer($this->dic);

        $factory = EntityAccessor::call($ctrl, 'responseFactory', $ctrl);

        $this->assertInstanceOf(ResponseFactory::class, $factory);
    }

    /**
     * @group controllers
     * @group controllers-api
     */
    public function testArgumentHelper()
    {
        $ctrl = new TestApiController();
        $ctrl->setContainer($this->dic);

        $factory = EntityAccessor::call($ctrl, 'requestArgumentHelper', $ctrl);

        $this->assertInstanceOf(RequestArgumentHelper::class, $factory);
    }

    /**
     * @group controllers
     * @group controllers-api
     */
    public function testItem()
    {
        $ctrl = new TestApiController();
        $ctrl->setContainer($this->dic);

        $response = $ctrl->returnItemResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @group controllers
     * @group controllers-api
     */
    public function testPaginate()
    {
        $ctrl = new TestApiController();
        $ctrl->setContainer($this->dic);

        $response = $ctrl->returnPaginatedResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @group controllers
     * @group controllers-api
     */
    public function testCollection()
    {
        $ctrl = new TestApiController();
        $ctrl->setContainer($this->dic);

        $response = $ctrl->returnCollectionResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @group controllers
     * @group controllers-api
     */
    public function testCreated()
    {
        $ctrl = new TestApiController();
        $ctrl->setContainer($this->dic);

        $transformer = TransformerBinding::item(new MyEntity(1, 'test', 'test 2', DateTime::now()), new MyEntityTransformer());
        $response    = $ctrl->created($transformer);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    /**
     * @group controllers
     * @group controllers-api
     */
    public function testUpdated()
    {
        $ctrl = new TestApiController();
        $ctrl->setContainer($this->dic);

        $transformer = TransformerBinding::item(new MyEntity(1, 'test', 'test 2', DateTime::now()), new MyEntityTransformer());
        $response    = $ctrl->updated($transformer);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @group controllers
     * @group controllers-api
     */
    public function testDeleted()
    {
        $ctrl = new TestApiController();
        $ctrl->setContainer($this->dic);

        $response = $ctrl->deleted(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /**
     * @group controllers
     * @group controllers-api
     */
    public function testNoContent()
    {
        $ctrl = new TestApiController();
        $ctrl->setContainer($this->dic);

        $response = $ctrl->noContent();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
