<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\ResourceAbstract;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasFields;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasIncludes;
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;
use Somnambulist\Components\Collection\Contracts\Collection;

class CollectionType extends AbstractType
{
    private Collection $resource;

    public function __construct(
        Collection $resource,
        string $transformer,
        string $key = 'data',
        array $includes = [],
        array $fields = [],
        array $meta = []
    ) {
        $this->assertIncludeArrayIsValid($includes);
        $this->assertFieldArrayIsValid($fields);

        $this->resource    = $resource;
        $this->transformer = $transformer;
        $this->key         = $key;
        $this->includes    = $includes;
        $this->fields      = $fields;
        $this->meta        = $meta;
    }

    public static function fromFormRequest(FormRequest $request, Collection $resource, string $transformer, string $key = 'data', array $meta = []): self
    {
        return new self(
            $resource,
            $transformer,
            $key,
            $request instanceof HasIncludes ? $request->includes() : [],
            $request instanceof HasFields ? $request->fields() : [],
            $meta
        );
    }

    public function asResource(): ResourceAbstract
    {
        $item = new FractalCollection($this->resource, $this->transformer, $this->key);
        $item->setMeta($this->meta);

        return $item;
    }

    public function resource(): Collection
    {
        return $this->resource;
    }
}
