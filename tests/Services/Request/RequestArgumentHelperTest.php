<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Services\Request;

use PHPUnit\Framework\TestCase;
use Somnambulist\ApiBundle\Services\Request\RequestArgumentHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestArgumentHelperTest
 *
 * @package Somnambulist\ApiBundle\Tests\Services\Request
 * @subpackage Somnambulist\ApiBundle\Tests\Services\Request\RequestArgumentHelperTest
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
}
