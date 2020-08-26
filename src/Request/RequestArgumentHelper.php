<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Request;

use Somnambulist\Collection\MutableCollection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use function array_filter;
use function count;
use function explode;
use function min;
use function strpos;
use function substr;
use function trim;

/**
 * Class RequestArgumentHelper
 *
 * Provides a set of methods for extracting page, per_page, limit and offset from a
 * Request object. The perPage, maxPerPage and limit can be configured as a service
 * setting for consistency or overridden when needed.
 *
 * @package    Somnambulist\ApiBundle
 * @subpackage Somnambulist\ApiBundle\Request\RequestArgumentHelper
 */
final class RequestArgumentHelper
{

    private int $perPage;
    private int $maxPerPage;
    private int $limit;

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
        return array_filter(explode(',', $request->query->get('include', '')));
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
        $fields = $this->convertOrderByStringToArrayValues($request->query->get('order', ''));

        if (empty($fields) && !is_null($default)) {
            return $this->convertOrderByStringToArrayValues($default);
        }

        return $fields;
    }

    private function convertOrderByStringToArrayValues(string $string): array
    {
        $fields = [];

        foreach (array_filter(explode(',', $string)) as $field) {
            if (0 === strpos(trim($field), '-')) {
                $fields[substr(trim($field), 1)] = 'DESC';
            } else {
                $fields[trim($field)] = 'ASC';
            }
        }

        return $fields;
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
        $page = $request->get('page', 1);

        return (int)($page < 1 ? 1 : $page);
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
        $limit = $request->get('per_page', $default = $this->valueOrDefault($default, $this->perPage));

        return (int)($limit < 1 ? $default : min($limit, $this->valueOrDefault($max, $this->maxPerPage)));
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
        $limit = $request->get('limit', $default = $this->valueOrDefault($default, $this->limit));

        return (int)($limit < 1 ? $default : min($limit, $this->valueOrDefault($max, $this->maxPerPage)));
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
        return (int)($this->page($request) - 1) * $this->valueOrDefault($limit, $this->limit);
    }

    /**
     * Returns either null or, all the fields specified or the fields in an object
     *
     * Note: to use class, the fields must be in constructor order and the constructor must
     * be simple scalars only. This will not hydrate a nested object.
     *
     * @param ParameterBag $request
     * @param array        $fields An array of fields required for this value
     * @param string|null  $class  An optional class to instantiate using the fields
     *
     * @return null|mixed
     */
    public function nullOrValue(ParameterBag $request, array $fields, string $class = null)
    {
        $data = MutableCollection::create($request->all());

        if (!$data->has(...$fields)) {
            return null;
        }

        if ($class) {
            return new $class(...$data->only(...$fields)->values()->toArray());
        }

        if (count($fields) === 1) {
            return $data->get(...$fields);
        }

        return $data->only(...$fields)->toArray();
    }

    private function valueOrDefault(?int $value, int $default): int
    {
        return $value ?? $default;
    }
}
