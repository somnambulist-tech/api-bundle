<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use Closure;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceAbstract;
use Pagerfanta\Pagerfanta;
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;

use function array_fill_keys;
use function array_merge;
use function http_build_query;
use function parse_str;
use function parse_url;
use function sprintf;

class PagerfantaType extends AbstractType
{
    private Pagerfanta $resource;
    private string $url;

    public function __construct(
        Pagerfanta $resource,
        string $transformer,
        string $url,
        string $key = 'data',
        array $includes = [],
        array $fields = [],
        array $meta = []
    ) {
        $this->assertIncludeArrayIsValid($includes);
        $this->assertFieldArrayIsValid($fields);

        $this->resource    = $resource;
        $this->transformer = $transformer;
        $this->url         = $url;
        $this->key         = $key;
        $this->includes    = $includes;
        $this->fields      = $fields;
        $this->meta        = $meta;
    }

    public static function fromFormRequest(FormRequest $request, Pagerfanta $resource, string $transformer, string $key = 'data', array $meta = []): self
    {
        $obj = new self($resource, $transformer, $request->source()->getUri(), $key, $request->includes(), $request->fields(), $meta);

        return $obj;
    }

    public function asResource(): ResourceAbstract
    {
        $item = new Collection($this->resource, $this->transformer, $this->key);
        $item->setMeta($this->meta);
        $item->setPaginator(new PagerfantaPaginatorAdapter($this->resource, $this->createRouteGenerator()));

        return $item;
    }

    public function resource(): Pagerfanta
    {
        return $this->resource;
    }

    public function url(): string
    {
        return $this->url;
    }

    private function createRouteGenerator(): Closure
    {
        return function ($page) {
            $query = [];
            $url   = array_merge(
                array_fill_keys(['scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'], null),
                parse_url($this->url())
            );

            parse_str($url['query'] ?? '', $query);

            $query['page'] = $page;

            return sprintf('%s://%s%s?%s', $url['scheme'], $url['host'], $url['path'], http_build_query($query));
        };
    }
}
