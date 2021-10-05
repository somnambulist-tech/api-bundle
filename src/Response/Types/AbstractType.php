<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use League\Fractal\Resource\ResourceAbstract;
use Somnambulist\Bundles\ApiBundle\Response\ResponseTypeInterface;
use function trigger_deprecation;

/**
 * Class ObjectResponse
 *
 * @package    Somnambulist\Bundles\ApiBundle\Response\Types
 * @subpackage Somnambulist\Bundles\ApiBundle\Response\Types\ObjectResponse
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

    public function withIncludes(...$includes): self
    {
        if (count($includes) === 1 && is_array($includes[0])) {
            trigger_deprecation('somnambulist/api-bundle', '3.6.0', 'Passing an array of includes is deprecated. Use multiple string arguments');
            $includes = $includes[0];
        }

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
