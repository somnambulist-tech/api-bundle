<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\ResourceAbstract;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasFields;
use Somnambulist\Bundles\ApiBundle\Request\Contracts\HasIncludes;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

class IterableType extends AbstractType
{
    private iterable $resource;

    public function __construct(
        iterable $resource,
        string $transformer,
        ?string $key = 'data',
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

    public static function fromFormRequest(FormRequest $request, iterable $resource, string $transformer, ?string $key = 'data', array $meta = []): self
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

    public function resource(): iterable
    {
        return $this->resource;
    }
}
