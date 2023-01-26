<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request;

use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetFieldsFromValidatedData;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetIncludesFromValidatedData;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasFields;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasFilters;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest as BaseFormRequest;

/**
 * View specific form request that includes common arguments for generating view data.
 *
 * Note: when using this form request it must be correctly validated before accessing the
 * values, otherwise they will be empty or defaults. This is handled automatically by the
 * form request argument resolver.
 *
 * When using this type of form request, you should ensure that the fields and includes are
 * sufficiently validated as they will be passed through to the query bus.
 */
abstract class ViewFormRequest extends BaseFormRequest implements HasFields, HasFilters
{
    use GetFieldsFromValidatedData;
    use GetIncludesFromValidatedData;
}
