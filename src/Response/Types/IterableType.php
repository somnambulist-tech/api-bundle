<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\ResourceAbstract;

/**
 * Class IterableType
 *
 * @package    Somnambulist\Bundles\ApiBundle\Response\Types
 * @subpackage Somnambulist\Bundles\ApiBundle\Response\Types\IterableType
 */
class IterableType extends AbstractType
{

    private iterable $resource;

    public function __construct(iterable $resource, string $transformer, array $meta = [], string $key = 'data')
    {
        $this->resource    = $resource;
        $this->transformer = $transformer;
        $this->key         = $key;
        $this->meta        = $meta;
    }

    public function asResource(): ResourceAbstract
    {
        $item = new FractalCollection($this->resource, $this->transformer, $this->key);
        $item->setMeta($this->meta);

        return $item;
    }

    public function getResource(): iterable
    {
        return $this->resource;
    }
}
