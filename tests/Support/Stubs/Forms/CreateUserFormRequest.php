<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\ApiBundle\Services\Attributes\OpenApiExamples;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

/**
 * Class CreateUserFormRequest
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\CreateUserFormRequest
 */
class CreateUserFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account_id'         => 'required|uuid',
            'email'              => 'required|email|min:3|max:60',
            'password'           => 'required|min:1|max:255',
            'name'               => 'required|min:1|max:255',
            'roles'              => 'array',
            'roles.*.id'         => 'required|min:1|max:255',
            'roles.*.name'       => 'min:1|max:255',
            'permissions'        => 'array',
            'permissions.*.id'   => ['required', 'min:1', 'max:255'],
            'permissions.*.name' => ['min:1', 'max:255'],
            'spare'              => 'array',
            'spare.*'            => 'min:1|max:255',
        ];
    }

    #[OpenApiExamples]
    public function examples(): array
    {
        return [
            'default' => [
                'summary' => 'A basic User with all required fields',
                'value'   => [
                    'account_id' => '59b8ccbd-ac5d-436d-9f1b-02e9576faf47',
                    'email'      => 'foo@bar',
                    'password'   => 'bcrypt hashed string',
                    'name'       => 'Foo Bar',
                ],
            ],
            'roles'   => [
                'summary' => 'A User with all roles that should be granted',
                'value'   => [
                    'account_id' => '59b8ccbd-ac5d-436d-9f1b-02e9576faf47',
                    'email'      => 'foo@bar',
                    'password'   => 'bcrypt hashed string',
                    'name'       => 'Foo Bar',
                    'roles'      => [
                        [
                            'id' => '9fc27d7c-22f7-43fe-9f1c-deafc971c95e',
                        ],
                    ],
                ],
            ],
        ];
    }
}
