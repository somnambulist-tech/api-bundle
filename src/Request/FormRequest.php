<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request;

use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetIncludesFromParameterBag;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetOrderByFromParameterBag;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetPaginationFromParameterBag;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest as BaseFormRequest;

/**
 * Class FormRequest
 *
 * @package    Somnambulist\Bundles\ApiBundle\Request
 * @subpackage Somnambulist\Bundles\ApiBundle\Request\FormRequest
 */
class FormRequest extends BaseFormRequest
{
    use GetIncludesFromParameterBag;
    use GetPaginationFromParameterBag;
    use GetOrderByFromParameterBag;

    public function includes(): array
    {
        return $this->doGetIncludes($this->query);
    }

    public function orderBy(string $default = null): array
    {
        return $this->doGetOrderBy($this->query, $default);
    }

    public function page(): int
    {
        return $this->doGetPage($this->query);
    }

    public function perPage(int $default = null, int $max = null): int
    {
        return $this->doGetPerPage($this->query, $default, $max);
    }

    public function offset(int $limit = null): int
    {
        return $this->doGetOffset($this->query, $limit);
    }

    public function limit(int $default = null, int $max = null): int
    {
        return $this->doGetLimit($this->query, $default, $max);
    }
}
