<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Somnambulist\Bundles\FormRequestBundle\Http\ValidatedDataBag;

use function array_filter;

/**
 * @method ValidatedDataBag data()
 */
trait GetFieldsFromValidatedData
{
    public function fields(): array
    {
        return array_filter($this->data()->get('fields', []));
    }
}
