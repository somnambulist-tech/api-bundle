<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use InvalidArgumentException;
use League\Fractal\Resource\ResourceAbstract;
use Somnambulist\Bundles\ApiBundle\Response\ResponseTypeInterface;
use function array_is_list;
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
    protected array $fields = [];
    protected array $meta = [];

    abstract public function asResource(): ResourceAbstract;

    public function withKey(string $key): self
    {
        trigger_deprecation('somnambulist/api-bundle', '3.8.0', 'Use key() instead');

        return $this->key($key);
    }

    public function key(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function withIncludes(...$includes): self
    {
        trigger_deprecation('somnambulist/api-bundle', '3.8.0', 'Use include() instead');

        if (count($includes) === 1 && is_array($includes[0])) {
            trigger_deprecation('somnambulist/api-bundle', '3.6.0', 'Passing an array of includes is deprecated. Use multiple string arguments');
            $includes = $includes[0];
        }

        return $this->include(...$includes);
    }

    public function include(string ...$includes): self
    {
        $this->includes = $includes;

        return $this;
    }

    public function fields(array $fields): self
    {
        if (array_is_list($fields)) {
            throw new InvalidArgumentException('fields must be an associative array of key names and comma separated string of fields');
        }

        foreach ($fields as $key => $field) {
            if (!is_string($field)) {
                throw new InvalidArgumentException(sprintf('The field "%s" does not have a valid value; it should be a string', $key));
            }
        }

        $this->fields = $fields;

        return $this;
    }

    public function withMeta(array $meta): self
    {
        trigger_deprecation('somnambulist/api-bundle', '3.8.0', 'Use meta() instead');

        return $this->meta($meta);
    }

    public function meta(array $meta): self
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

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
