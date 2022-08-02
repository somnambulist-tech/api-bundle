<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request;

use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetFieldsFromParameterBag;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetIncludesFromParameterBag;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetOrderByFromParameterBag;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetPaginationFromParameterBag;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest as BaseFormRequest;

class FormRequest extends BaseFormRequest
{
    use GetFieldsFromParameterBag;
    use GetIncludesFromParameterBag;
    use GetPaginationFromParameterBag;
    use GetOrderByFromParameterBag;

    public function includes(): array
    {
        return $this->doGetIncludes($this->query);
    }

    public function fields(): array
    {
        return $this->doGetFields($this->query);
    }

    public function orderBy(string $default = null): array
    {
        return $this->doGetOrderBy($this->query, $this->data()->get('order', $default));
    }

    public function page(): int
    {
        return $this->doGetPage($this->query);
    }

    public function perPage(int $default = null, int $max = null): int
    {
        return $this->doGetPerPage($this->query, $this->data()->get('per_page', $default), $max);
    }

    public function offset(int $limit = null): int
    {
        return $this->doGetOffset($this->query, $limit);
    }

    public function limit(int $default = null, int $max = null): int
    {
        return $this->doGetLimit($this->query, $this->data()->get('limit', $default), $max);
    }
}
