<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Request;

use Symfony\Component\HttpFoundation\Request;
use function array_filter;
use function explode;
use function min;

/**
 * Class RequestArgumentHelper
 *
 * Provides a set of methods for extracting page, per_page, limit and offset from a
 * Request object. The perPage, maxPerPage and limit can be configured as a service
 * setting for consistency or overridden when needed.
 *
 * @package Somnambulist\ApiBundle\Services
 * @subpackage Somnambulist\ApiBundle\Services\Request\RequestArgumentHelper
 */
final class RequestArgumentHelper
{

    /**
     * @var int
     */
    private $perPage = 20;

    /**
     * @var int
     */
    private $maxPerPage = 100;

    /**
     * @var int
     */
    private $limit = 100;

    /**
     * Constructor.
     *
     * @param int $perPage
     * @param int $maxPerPage
     * @param int $limit
     */
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
     * @param int|null $max Provide a different max to the preconfigured one
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
     * @param int|null $max Provide a different max to the preconfigured one
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

    private function valueOrDefault(?int $value, int $default): int
    {
        return $value ?? $default;
    }
}
