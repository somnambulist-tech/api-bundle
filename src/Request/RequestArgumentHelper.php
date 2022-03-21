<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request;

use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetIncludesFromParameterBag;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetNullOrValueFromParameterBag;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetOrderByFromParameterBag;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetPaginationFromParameterBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestArgumentHelper
 *
 * Provides a set of methods for extracting page, per_page, limit and offset from a
 * Request object. The perPage, maxPerPage and limit can be configured as a service
 * setting for consistency or overridden when needed.
 *
 * @package    Somnambulist\Bundles\ApiBundle
 * @subpackage Somnambulist\Bundles\ApiBundle\Request\RequestArgumentHelper
 * @deprecated 3.8.0 Use form requests instead
 */
final class RequestArgumentHelper
{
    use GetIncludesFromParameterBag;
    use GetOrderByFromParameterBag;
    use GetPaginationFromParameterBag;
    use GetNullOrValueFromParameterBag;

    public function __construct(int $perPage = 20, int $maxPerPage = 100, int $limit = 100)
    {
        $this->perPage    = $perPage;
        $this->maxPerPage = $maxPerPage;
        $this->limit      = $limit;
    }

    /**
     * Returns any requested data includes as an array
     *
     * @param Request $request
     *
     * @return array
     */
    public function includes(Request $request): array
    {
        return $this->doGetIncludes($request->query);
    }

    /**
     * Returns an array of fields to order by
     *
     * Expects a request argument of `order` that is a comma separated string of field names.
     * If the field is prefixed with a - (hyphen / minus) then the order will be DESC, otherwise it
     * defaults to ASC.
     *
     * A default order by can be specified that will be used if there are no fields in the request.
     * This default should be in the same format as the request variable e.g.: name,created_at or
     * -updated_at
     *
     * @param Request     $request
     * @param string|null $default (optional) the default order by string
     *
     * @return array
     */
    public function orderBy(Request $request, string $default = null): array
    {
        return $this->doGetOrderBy($request->query, $default);
    }

    /**
     * Returns the current page number from the Request
     *
     * @param Request $request
     *
     * @return int
     */
    public function page(Request $request): int
    {
        return $this->doGetPage($request->query);
    }

    /**
     * Returns the per_page value and ensures it is less than the max per page
     *
     * @param Request  $request
     * @param int|null $default Provide a different per_page to the preconfigured one
     * @param int|null $max     Provide a different max to the preconfigured one
     *
     * @return int
     */
    public function perPage(Request $request, int $default = null, int $max = null): int
    {
        return $this->doGetPerPage($request->query, $default, $max);
    }

    /**
     * Returns the limit from the Request object and ensures it is between the limits
     *
     * @param Request  $request
     * @param int|null $default Provide a different limit to the preconfigured one
     * @param int|null $max     Provide a different max to the preconfigured one
     *
     * @return int
     */
    public function limit(Request $request, int $default = null, int $max = null): int
    {
        return $this->doGetLimit($request->query, $default, $max);
    }

    /**
     * Returns an offset value suitable for use with an SQL LIMIT clause e.g. page=1 would return 0
     *
     * @param Request  $request
     * @param int|null $limit Provide a different limit to the preconfigured one
     *
     * @return int
     */
    public function offset(Request $request, int $limit = null): int
    {
        return $this->doGetOffset($request->query, $limit);
    }

    /**
     * Returns either null or, all the fields specified or the fields in an object
     *
     * Note: to use class, the fields must be in constructor order and the constructor must
     * be simple scalars only. This will not hydrate a nested object.
     *
     * @param ParameterBag $request
     * @param array        $fields  An array of fields required for this value
     * @param string|null  $class   An optional class to instantiate using the fields
     * @param bool         $subNull If true, substitutes null for missing fields
     *
     * @return mixed
     */
    public function nullOrValue(ParameterBag $request, array $fields, string $class = null, bool $subNull = false): mixed
    {
        return $this->doNullOrValue($request, $fields, $class, $subNull);
    }
}
