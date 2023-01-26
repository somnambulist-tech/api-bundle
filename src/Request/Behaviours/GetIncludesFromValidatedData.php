<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Behaviours;

use Somnambulist\Bundles\FormRequestBundle\Http\ValidatedDataBag;

use function array_filter;
use function explode;

/**
 * @method ValidatedDataBag data()
 */
trait GetIncludesFromValidatedData
{
    public function includes(): array
    {
        return array_filter(explode(',', $this->data()->get('include', '')));
    }
}
