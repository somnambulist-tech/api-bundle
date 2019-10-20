<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Transformer;

use IteratorAggregate;

/**
 * Class TransformerBinding
 *
 * Based on Dingo API Transformer/Binding class.
 *
 * @package    Somnambulist\ApiBundle\Services\Transformer
 * @subpackage Somnambulist\ApiBundle\Services\Transformer\TransformerBinding
 */
final class TransformerBinding
{

    /**
     * @var mixed
     */
    private $resource;

    /**
     * @var mixed
     */
    private $transformer;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $includes = [];

    /**
     * @var array
     */
    private $meta = [];

    /**
     * Constructor.
     *
     * @param mixed       $resource The resource to be transformed, an object
     * @param callable    $transformer A callable or class name to transform the resource
     * @param null|string $key An object property name to place the transformed data under
     * @param null|string $url The URL to this resource (only for paginators)
     */
    private function __construct($resource, $transformer, string $key = null, string $url = null)
    {
        $this->resource    = $resource;
        $this->transformer = $transformer;
        $this->key         = $key;
        $this->url         = $url;
    }

    /**
     * @param object   $collection
     * @param callable $transformer
     *
     * @return static
     */
    public static function collection(object $collection, $transformer): self
    {
        return new static($collection, $transformer, 'data', null);
    }

    /**
     * @param object   $collection
     * @param callable $transformer
     * @param string   $url The base URL for paginating, ?page=X will be added
     *
     * @return static
     */
    public static function paginate(object $collection, $transformer, string $url = null): self
    {
        return new static($collection, $transformer, 'data', $url);
    }

    /**
     * @param mixed    $item
     * @param callable $transformer
     *
     * @return static
     */
    public static function item($item, $transformer): self
    {
        return new static($item, $transformer);
    }



    public function withKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function withUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function withIncludes(array $includes): self
    {
        $this->includes = $includes;

        return $this;
    }

    public function withMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }



    public function getResource()
    {
        return $this->resource;
    }

    public function getTransformer()
    {
        return $this->transformer;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getIncludes(): array
    {
        return $this->includes;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getType(): string
    {
        return $this->resource instanceof IteratorAggregate ? 'collection' : 'item';
    }
}
