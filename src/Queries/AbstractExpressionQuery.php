<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Queries;

use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression;
use Somnambulist\Components\Collection\FrozenCollection;
use Somnambulist\Components\Queries\AbstractQuery;
use Somnambulist\Components\Queries\Behaviours\CanIncludeMetaData;
use Somnambulist\Components\Queries\Behaviours\CanIncludeRelatedData;
use Somnambulist\Components\Queries\Behaviours\CanPaginateQuery;
use Somnambulist\Components\Queries\Behaviours\CanSortQuery;
use Somnambulist\Components\Queries\Contracts\Paginatable;
use Somnambulist\Components\Queries\Contracts\Sortable;

/**
 * Similar to the paginatable query from the domain library, except uses the Api Expression
 * as the criteria storage.
 */
abstract class AbstractExpressionQuery extends AbstractQuery implements Sortable, Paginatable
{
    use CanIncludeRelatedData;
    use CanIncludeMetaData;
    use CanSortQuery;
    use CanPaginateQuery;

    public function __construct(
        private readonly CompositeExpression $where,
        array $orderBy = [],
        int $page = 1,
        int $perPage = 30
    ) {
        $this->orderBy  = new FrozenCollection($orderBy);
        $this->page     = $page;
        $this->perPage  = $perPage;
    }

    public function where(): CompositeExpression
    {
        return $this->where;
    }
}
