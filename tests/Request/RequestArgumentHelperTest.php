<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Request\RequestArgumentHelper;
use Somnambulist\Components\Domain\Entities\Types\Identity\ExternalIdentity;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestArgumentHelperTest
 *
 * @package Somnambulist\Bundles\ApiBundle\Tests\Request
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Request\RequestArgumentHelperTest
 */
class RequestArgumentHelperTest extends TestCase
{

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestPage()
    {
        $helper = new RequestArgumentHelper();

        $this->assertEquals(1, $helper->page(Request::createFromGlobals()));
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestPageReturnsOneForNegativeValues()
    {
        $helper = new RequestArgumentHelper();

        $page = $helper->page(Request::create('/', 'GET', ['page' => '-234']));

        $this->assertEquals(1, $page);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestPerPageLimitReturnsInRange()
    {
        $helper = new RequestArgumentHelper();

        $limit = $helper->perPage(Request::create('/', 'GET', ['per_page' => '-90']));

        $this->assertEquals(20, $limit);

        $limit = $helper->perPage(Request::create('/', 'GET', ['per_page' => '1000']));

        $this->assertEquals(100, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestLimit()
    {
        $helper = new RequestArgumentHelper();

        $limit = $helper->limit(Request::createFromGlobals());

        $this->assertEquals(100, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestLimitReturnsInRange()
    {
        $helper = new RequestArgumentHelper();

        $limit = $helper->limit(Request::create('/', 'GET', ['limit' => '-90']));

        $this->assertEquals(100, $limit);

        $limit = $helper->limit(Request::create('/', 'GET', ['limit' => '1000']));

        $this->assertEquals(100, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetRequestOffsetFromPage()
    {
        $helper = new RequestArgumentHelper();

        $limit = $helper->offset(Request::createFromGlobals(), 20);

        $this->assertEquals(0, $limit);

        $limit = $helper->offset(Request::create('/', 'GET', ['page' => 10]), 20);

        $this->assertEquals(9*20, $limit);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testGetIncludesFromRequest()
    {
        $helper = new RequestArgumentHelper();

        $includes = $helper->includes(Request::create('/', 'GET', ['include' => 'foo,bar,baz,baz.foo.bar']));

        $this->assertIsArray($includes);
        $this->assertEquals(['foo', 'bar', 'baz', 'baz.foo.bar'], $includes);

        $includes = $helper->includes(Request::createFromGlobals());

        $this->assertIsArray($includes);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testNullOrValue()
    {
        $helper = new RequestArgumentHelper();

        $var = $helper->nullOrValue(Request::create('/', 'GET', ['provider' => 'bob', 'identity' => 'foo'])->query, ['provider']);

        $this->assertEquals('bob', $var);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testNullOrValueReturnsAllFields()
    {
        $helper = new RequestArgumentHelper();

        $var = $helper->nullOrValue(Request::create('/', 'GET', ['provider' => 'bob', 'identity' => 'foo'])->query, ['provider', 'identity']);

        $this->assertIsArray($var);
        $this->assertEquals(['provider' => 'bob', 'identity' => 'foo'], $var);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testNullOrValueReturnsNullIfMissingField()
    {
        $helper = new RequestArgumentHelper();

        $var = $helper->nullOrValue(Request::create('/', 'GET', ['provider' => 'bob', ])->query, ['provider', 'identity']);

        $this->assertNull($var);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testNullOrValueIntoClass()
    {
        $helper = new RequestArgumentHelper();

        $var = $helper->nullOrValue(Request::create('/', 'GET', ['provider' => 'bob', 'identity' => 'foo'])->query, ['provider', 'identity'], ExternalIdentity::class);

        $this->assertInstanceOf(ExternalIdentity::class, $var);
        $this->assertEquals('bob', $var->provider());
        $this->assertEquals('foo', $var->identity());
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testOrderBy()
    {
        $helper = new RequestArgumentHelper();

        $orderBy = $helper->orderBy(Request::create('/', 'GET', ['order' => 'id,-created_at,-name']));

        $this->assertEquals(['id' => 'ASC', 'created_at' => 'DESC', 'name' => 'DESC'], $orderBy);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     */
    public function testOrderByReturnsEmptyArrayIfNotSpecified()
    {
        $helper = new RequestArgumentHelper();

        $orderBy = $helper->orderBy(Request::create('/', 'GET'));

        $this->assertEquals([], $orderBy);
    }

    /**
     * @group behaviours
     * @group behaviours-request
     * @group cur
     */
    public function testOrderByReturnsTheDefaultIfSet()
    {
        $helper = new RequestArgumentHelper();

        $orderBy = $helper->orderBy(Request::create('/', 'GET'), 'foobar,-baz');

        $this->assertEquals(['foobar' => 'ASC', 'baz' => 'DESC'], $orderBy);
    }
}
