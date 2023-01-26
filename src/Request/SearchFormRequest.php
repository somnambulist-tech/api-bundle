<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request;

use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetFieldsFromValidatedData;
use Somnambulist\Bundles\ApiBundle\Request\Behaviours\GetIncludesFromValidatedData;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasFields;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasFilters;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasIncludes;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasMarker;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasOffsetLimit;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasPagination;
use Somnambulist\Bundles\ApiBundle\Request\Filters\ConvertOrderByToArray;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest as BaseFormRequest;

use function array_filter;
use function is_null;
use function max;
use function min;

/**
 * Search specific form request that includes common pagination / search arguments.
 *
 * Note: when using this form request it must be correctly validated before accessing the
 * values, otherwise they will be empty or defaults. This is handled automatically by the
 * form request argument resolver.
 *
 * Unlike the previous FormRequest, offset/limit are treated separately from page/perPage.
 * You should use one or the other. offset/limit may be limited to marker/limit instead of offset.
 * The difference is that marker can be a string value, unlike offset being an integer.
 */
abstract class SearchFormRequest extends BaseFormRequest implements HasFields, HasFilters, HasIncludes, HasMarker, HasOffsetLimit, HasPagination
{
    use GetFieldsFromValidatedData;
    use GetIncludesFromValidatedData;

    protected int $perPage = 20;
    protected int $maxPerPage = 100;
    protected int $limit = 100;

    /**
     * Auto-created default pagination rules for all standard pagination arguments
     *
     * @return array
     */
    protected function paginationRules(): array
    {
        return [
            'page'     => 'sometimes|numeric|min:1',
            'per_page' => sprintf('sometimes|numeric|default:%d|min:1|max:%d', $this->perPage, $this->maxPerPage),
            'offset'   => 'sometimes|numeric|default:0',
            'marker'   => 'sometimes|string|min:1',
            'limit'    => sprintf('sometimes|numeric|default:%d|min:1|max:%d', $this->limit, $this->maxPerPage),
        ];
    }

    public function filters(): array
    {
        return array_filter($this->data()->get('filters', $this->data()->get('filter', [])));
    }

    public function orderBy(string $default = null): array
    {
        $order = $this->data()->get('sort', $this->data()->get('order', $default));

        if (is_null($order)) {
            return [];
        }

        return (new ConvertOrderByToArray())($order);
    }

    public function page(): int
    {
        return (int)max($this->data()->getInt('page', 1), 1);
    }

    public function perPage(int $default = null, int $max = null): int
    {
        $limit = $this->data()->getInt('per_page', $default ?? $this->perPage);

        return ($limit < 1) ? ($default ?? $this->perPage) : min($limit, $max ?? $this->maxPerPage);
    }

    public function offset(): int
    {
        return max($this->data()->getInt('offset'), 0);
    }

    public function marker(): ?string
    {
        return $this->data()->get('marker');
    }

    public function limit(int $default = null, int $max = null): int
    {
        $limit = $this->data()->getInt('limit', $default ??= $this->limit);

        return $limit < 1 ? $default : min($limit, $max ?? $this->maxPerPage);
    }
}
