<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Request\SearchFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Components\Validation\Factory;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group request
 * @group request-api-request
 */
class SearchFormRequestTest extends TestCase
{
    private function request(array $query = []): SearchFormRequest
    {
        $form = new class(Request::create('/', 'GET', $query)) extends SearchFormRequest {
            public function rules(): array
            {
                return array_merge(
                    $this->paginationRules(),
                    [
                        'filters'      => 'sometimes|array|array_can_only_have_keys:id,name',
                        'filters.id'   => 'sometimes|uuid',
                        'filters.name' => 'sometimes|string|between:1,100',
                        'fields'       => 'sometimes|array',
                        'fields.*'     => 'sometimes|string',
                        'include'      => [
                            'sometimes',
                            'regex:/(users(.roles)?(.permissions)?)|(roles(.permissions)?)/',
                        ],
                        'order'        => [
                            'sometimes',
                            'regex:/-?(id|name|created_at|updated_at){1,},?/',
                        ],
                    ]
                );
            }
        };

        $validation = (new Factory)->validate($form->all(), $form->rules());

        FormRequest::appendValidationData($form, $validation->getValidData());

        return $form;
    }

    public function testGetRequestPage()
    {
        $helper = $this->request();

        $this->assertEquals(1, $helper->page());
    }

    public function testGetRequestPageReturnsOneForNegativeValues()
    {
        $helper = $this->request(['page' => '-234']);

        $page = $helper->page();

        $this->assertEquals(1, $page);
    }

    public function testGetRequestPerPageLimitReturnsInRange()
    {
        $helper = $this->request(['per_page' => '-90']);

        $perPage = $helper->perPage();

        $this->assertEquals(20, $perPage);

        $helper = $this->request(['per_page' => '1000']);

        $perPage = $helper->perPage();

        $this->assertEquals(20, $perPage);
    }

    public function testGetRequestLimit()
    {
        $helper = $this->request();

        $limit = $helper->limit();

        $this->assertEquals(100, $limit);
    }

    public function testGetRequestLimitReturnsInRange()
    {
        $helper = $this->request(['limit' => '-90']);

        $limit = $helper->limit();

        $this->assertEquals(100, $limit);

        $helper = $this->request(['limit' => '1000']);

        $limit = $helper->limit();

        $this->assertEquals(100, $limit);
    }

    public function testGetRequestOffsetFromOffsetIfSet()
    {
        $helper = $this->request();

        $offset = $helper->offset();

        $this->assertEquals(0, $offset);

        $helper = $this->request(['offset' => 10]);

        $offset = $helper->offset();

        $this->assertEquals(10, $offset);
    }

    public function testOffsetCannotBeLessThanZero()
    {
        $helper = $this->request(['offset' => -10]);

        $offset = $helper->offset();

        $this->assertEquals(0, $offset);
    }

    public function testOffsetIsOnlyObtainedFromOffsetArg()
    {
        $helper = $this->request();

        $offset = $helper->offset();

        $this->assertEquals(0, $offset);

        $helper = $this->request(['page' => 10]);

        $offset = $helper->offset();

        $this->assertEquals(0, $offset);
    }

    public function testGetIncludesFromRequest()
    {
        $helper = $this->request(['include' => 'users.roles,users.roles.permissions,roles']);

        $includes = $helper->includes();

        $this->assertIsArray($includes);
        $this->assertEquals(['users.roles', 'users.roles.permissions', 'roles'], $includes);

        $helper = $this->request();

        $includes = $helper->includes();

        $this->assertIsArray($includes);
    }

    public function testGetFieldsFromRequest()
    {
        $helper = $this->request(['fields' => ['object' => 'foo,bar,baz']]);

        $fields = $helper->fields();

        $this->assertIsArray($fields);
        $this->assertEquals(['object' => 'foo,bar,baz'], $fields);

        $helper = $this->request();

        $fields = $helper->fields();

        $this->assertIsArray($fields);
    }

    public function testGetOrderByFromRequest()
    {
        $helper = $this->request(['order' => '-created_at,name']);

        $fields = $helper->orderBy();

        $this->assertIsArray($fields);
        $this->assertEquals(['created_at' => 'DESC', 'name' => 'ASC'], $fields);

        $helper = $this->request();

        $fields = $helper->orderBy();

        $this->assertIsArray($fields);
    }

    public function testGetOrderByReturnsDefault()
    {
        $helper = $this->request();

        $fields = $helper->orderBy('name');

        $this->assertEquals(['name' => 'ASC'], $fields);
    }

    public function testGetOrderByHandlesAlternativeSyntax()
    {
        $helper = $this->request();

        $fields = $helper->orderBy('name:desc');

        $this->assertEquals(['name' => 'DESC'], $fields);
    }

    public function testGetOrderByAlternativeSyntaxOnlyUsesAscDesc()
    {
        $helper = $this->request();

        $fields = $helper->orderBy('name:down');

        $this->assertEquals(['name' => 'ASC'], $fields);
    }

    public function testGetFilters()
    {
        $helper = $this->request(['filters' => $a = ['id' => '4f4124fd-5f4c-42d4-b662-c502edef220a', 'name' => 'bob']]);

        $filters = $helper->filters();

        $this->assertIsArray($filters);
        $this->assertEquals($a, $filters);
    }

    public function testGetFiltersOnlyReturnsValidatedFilters()
    {
        $helper = $this->request(['filters' => ['id' => '4f4124fd-5f4c-42d4-b662-c502edef220a', 'name' => 'bob', 'foo' => 'baz']]);

        $filters = $helper->filters();

        $this->assertIsArray($filters);
        $this->assertArrayNotHasKey('foo', $filters);
    }

    public function testGetMarker()
    {
        $helper = $this->request(['marker' => $s = 'f7360783-83c7-43a2-ae34-40c5ff37a6fd']);

        $this->assertEquals($s, $helper->marker());
    }
}
