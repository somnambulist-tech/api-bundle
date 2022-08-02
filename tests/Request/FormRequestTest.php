<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Request;

use Somnambulist\Bundles\ApiBundle\Request\FormRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class FormRequestTest extends TestCase
{

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestPage()
    {
        $helper = new FormRequest(Request::createFromGlobals());

        $this->assertEquals(1, $helper->page());
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestPageReturnsOneForNegativeValues()
    {
        $helper = new FormRequest(Request::create('/', 'GET', ['page' => '-234']));

        $page = $helper->page();

        $this->assertEquals(1, $page);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestPerPageLimitReturnsInRange()
    {
        $helper = new FormRequest(Request::create('/', 'GET', ['per_page' => '-90']));

        $limit = $helper->perPage();

        $this->assertEquals(20, $limit);

        $helper = new FormRequest(Request::create('/', 'GET', ['per_page' => '1000']));

        $limit = $helper->perPage();

        $this->assertEquals(100, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestLimit()
    {
        $helper = new FormRequest(Request::createFromGlobals());

        $limit = $helper->limit();

        $this->assertEquals(100, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestLimitReturnsInRange()
    {
        $helper = new FormRequest(Request::create('/', 'GET', ['limit' => '-90']));

        $limit = $helper->limit();

        $this->assertEquals(100, $limit);

        $helper = new FormRequest(Request::create('/', 'GET', ['limit' => '1000']));

        $limit = $helper->limit();

        $this->assertEquals(100, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestOffsetFromOffsetIfSet()
    {
        $helper = new FormRequest(Request::createFromGlobals());

        $limit = $helper->offset(20);

        $this->assertEquals(0, $limit);

        $helper = new FormRequest(Request::create('/', 'GET', ['offset' => 10]));

        $limit = $helper->offset(20);

        $this->assertEquals(10, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testOffsetCannotBeLessThanZero()
    {
        $helper = new FormRequest(Request::create('/', 'GET', ['offset' => -10]));

        $limit = $helper->offset(20);

        $this->assertEquals(0, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestOffsetFromPage()
    {
        $helper = new FormRequest(Request::createFromGlobals());

        $limit = $helper->offset(20);

        $this->assertEquals(0, $limit);

        $helper = new FormRequest(Request::create('/', 'GET', ['page' => 10]));

        $limit = $helper->offset(20);

        $this->assertEquals(9*20, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetIncludesFromRequest()
    {
        $helper = new FormRequest(Request::create('/', 'GET', ['include' => 'foo,bar,baz,baz.foo.bar']));

        $includes = $helper->includes();

        $this->assertIsArray($includes);
        $this->assertEquals(['foo', 'bar', 'baz', 'baz.foo.bar'], $includes);

        $helper = new FormRequest(Request::createFromGlobals());

        $includes = $helper->includes();

        $this->assertIsArray($includes);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetFieldsFromRequest()
    {
        $helper = new FormRequest(Request::create('/', 'GET', ['fields' => ['object' => 'foo,bar,baz']]));

        $fields = $helper->fields();

        $this->assertIsArray($fields);
        $this->assertEquals(['object' => 'foo,bar,baz'], $fields);

        $helper = new FormRequest(Request::createFromGlobals());

        $fields = $helper->fields();

        $this->assertIsArray($fields);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetOrderByFromRequest()
    {
        $helper = new FormRequest(Request::create('/', 'GET', ['order' => '-created_at,name']));

        $fields = $helper->orderBy();

        $this->assertIsArray($fields);
        $this->assertEquals(['created_at' => 'DESC', 'name' => 'ASC'], $fields);

        $helper = new FormRequest(Request::createFromGlobals());

        $fields = $helper->orderBy();

        $this->assertIsArray($fields);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetOrderByReturnsDefault()
    {
        $helper = new FormRequest(Request::create('/'));

        $fields = $helper->orderBy('name');

        $this->assertEquals(['name' => 'ASC'], $fields);
    }
}
