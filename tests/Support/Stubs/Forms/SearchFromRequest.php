<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

/**
 * Class SearchFromRequest
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\SearchFromRequest
 */
class SearchFromRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'include' => 'string',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'email' => 'string|min:3|max:50',
            'name' => 'string|min:2|max:50',
        ];
    }
}
