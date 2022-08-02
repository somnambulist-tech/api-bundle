<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceAbstract;
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;

class ObjectType extends AbstractType
{
    private object $resource;

    public function __construct(object $resource, string $transformer, array $meta = [], string $key = null)
    {
        $this->resource    = $resource;
        $this->transformer = $transformer;
        $this->key         = $key;
        $this->meta        = $meta;
    }

    public static function fromFormRequest(FormRequest $request, object $resource, string $transformer, array $meta = [], string $key = null): self
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
        $item = new Item($this->resource, $this->transformer, $this->key);
        $item->setMeta($this->meta);

        return $item;
    }

    public function getResource(): object
    {
        return $this->resource;
    }
}
