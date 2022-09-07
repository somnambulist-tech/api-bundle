<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceAbstract;
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;

class ObjectType extends AbstractType
{
    private object $resource;

    public function __construct(
        object $resource,
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

    public static function fromFormRequest(FormRequest $request, object $resource, string $transformer, string $key = null, array $meta = []): self
    {
        $obj = new self($resource, $transformer, $key, $request->includes(), $request->fields(), $meta);

        return $obj;
    }

    public function asResource(): ResourceAbstract
    {
        $item = new Item($this->resource, $this->transformer, $this->key);
        $item->setMeta($this->meta);

        return $item;
    }

    public function resource(): object
    {
        return $this->resource;
    }
}
