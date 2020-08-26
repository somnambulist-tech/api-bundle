<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Response\Types;

use League\Fractal\Resource\ResourceAbstract;
use Somnambulist\ApiBundle\Response\ResponseTypeInterface;

/**
 * Class ObjectResponse
 *
 * @package    Somnambulist\ApiBundle\Response\Types
 * @subpackage Somnambulist\ApiBundle\Response\Types\ObjectResponse
 */
abstract class AbstractType implements ResponseTypeInterface
{

    protected string $transformer;
    protected ?string $key;
    protected array $includes = [];
    protected array $meta = [];

    abstract public function asResource(): ResourceAbstract;

    public function withKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function withIncludes(string ...$includes): self
    {
        $this->includes = $includes;

        return $this;
    }

    public function withMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getTransformer(): string
    {
        return $this->transformer;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getIncludes(): array
    {
        return $this->includes;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
