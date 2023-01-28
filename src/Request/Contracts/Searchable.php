<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Contracts;

interface Searchable extends HasFields, HasFilters, HasIncludes, HasMarker, HasOffsetLimit, HasPagination
{

}
