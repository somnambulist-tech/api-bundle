<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\ResourceAbstract;
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;
use Somnambulist\Components\Collection\Contracts\Collection;

class CollectionType extends AbstractType
{
    private Collection $resource;

    public function __construct(Collection $resource, string $transformer, array $meta = [], string $key = 'data')
    {
        $this->resource    = $resource;
        $this->transformer = $transformer;
        $this->key         = $key;
        $this->meta        = $meta;
    }

    public static function fromFormRequest(FormRequest $request, Collection $resource, string $transformer, array $meta = [], string $key = null): self
    {
        $obj = new self($resource, $transformer, $meta, $key);
        $obj
            ->include(...$request->includes())
            ->fields($request->fields())
        ;

        return $obj;
    }

    public function asResource(): ResourceAbstract
    {
        $item = new FractalCollection($this->resource, $this->transformer, $this->key);
        $item->setMeta($this->meta);

        return $item;
    }

    public function getResource(): Collection
    {
        return $this->resource;
    }
}
