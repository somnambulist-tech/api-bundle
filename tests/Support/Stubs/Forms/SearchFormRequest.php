<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\ApiBundle\Services\Attributes\OpenApiExamples;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

/**
 * Class SearchFormRequest
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\SearchFormRequest
 */
class SearchFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'include'   => ['sometimes', 'regex:/[(roles|permissions).,]/'],
            'order'     => ['default' => 'name', 'regex' => '/[(name|id|created_at),-]/',],
            'page'      => 'integer|default:1|min:1',
            'per_page'  => 'integer|min:1|max:100',
            'email'     => 'string|min:3|max:50',
            'name'      => [
                'string',
                'min:2',
                'max:50',
            ],
            'user_id'   => 'array',
            'user_id.*' => 'uuid',
            //'user_id.*.name' => 'string|max:100',
        ];
    }

    #[OpenApiExamples]
    public function examples(): array
    {
        return [
            'include'  => [
                'single_include'    => [
                    'summary' => 'Add the Roles in the response',
                    'value'   => 'roles',
                ],
                'multiple_includes' => [
                    'summary' => 'Add the Roles+Permissions and specific Permissions in the response',
                    'value'   => 'roles.permissions,permissions',
                ],
            ],
            'page'     => 5,
            'per_page' => 100,
            'name'     => 'foo',
        ];
    }
}
