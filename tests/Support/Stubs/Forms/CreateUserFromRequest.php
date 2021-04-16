<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

/**
 * Class CreateUserFromRequest
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\CreateUserFromRequest
 */
class CreateUserFromRequest extends FormRequest
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
            'permissions.*.name' => 'min:1|max:255',
            'spare'              => 'array',
            'spare.*'            => 'min:1|max:255',
        ];
    }
}
