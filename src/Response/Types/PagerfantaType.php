<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use Closure;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceAbstract;
use Pagerfanta\Pagerfanta;
use function array_fill_keys;
use function array_merge;
use function http_build_query;
use function parse_str;
use function parse_url;
use function sprintf;

/**
 * Class PagerfantaType
 *
 * @package    Somnambulist\Bundles\ApiBundle\Response\Types
 * @subpackage Somnambulist\Bundles\ApiBundle\Response\Types\PagerfantaType
 */
class PagerfantaType extends AbstractType
{

    private Pagerfanta $resource;
    private string $url;

    public function __construct(Pagerfanta $resource, string $transformer, string $url, array $meta = [], string $key = 'data')
    {
        $this->resource    = $resource;
        $this->transformer = $transformer;
        $this->url         = $url;
        $this->key         = $key;
        $this->meta        = $meta;
    }

    public function asResource(): ResourceAbstract
    {
        $item = new Collection($this->resource, $this->transformer, $this->key);
        $item->setMeta($this->meta);
        $item->setPaginator(new PagerfantaPaginatorAdapter($this->resource, $this->createRouteGenerator()));

        return $item;
    }

    public function getResource(): Pagerfanta
    {
        return $this->resource;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    private function createRouteGenerator(): Closure
    {
        return function ($page) {
            $query = [];
            $url   = array_merge(
                array_fill_keys(['scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'], null),
                parse_url($this->getUrl())
            );

            parse_str($url['query'] ?? '', $query);

            $query['page'] = $page;

            return sprintf('%s://%s%s?%s', $url['scheme'], $url['host'], $url['path'], http_build_query($query));
        };
    }
}
