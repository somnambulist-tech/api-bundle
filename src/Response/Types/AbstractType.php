<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Types;

use InvalidArgumentException;
use League\Fractal\Resource\ResourceAbstract;
use Somnambulist\Bundles\ApiBundle\Response\ResponseTypeInterface;

use function array_is_list;
use function is_string;
use function sprintf;

abstract class AbstractType implements ResponseTypeInterface
{
    protected string $transformer;
    protected ?string $key;
    protected array $includes = [];
    protected array $fields = [];
    protected array $meta = [];

    abstract public function asResource(): ResourceAbstract;

    protected function assertIncludeArrayIsValid(array $includes): void
    {
        foreach ($includes as $include) {
            if (!is_string($include)) {
                throw new InvalidArgumentException(sprintf('The include "%s" does not have a valid value; it should be a string', $include));
            }
        }
    }

    protected function assertFieldArrayIsValid(array $fields): void
    {
        if (!empty($fields) && array_is_list($fields)) {
            throw new InvalidArgumentException('fields must be an associative array of key names and comma separated string of fields');
        }

        foreach ($fields as $key => $field) {
            if (!is_string($field)) {
                throw new InvalidArgumentException(sprintf('The field "%s" does not have a valid value; it should be a string', $key));
            }
        }
    }

    public function transformer(): string
    {
        return $this->transformer;
    }

    public function key(): ?string
    {
        return $this->key;
    }

    public function includes(): array
    {
        return $this->includes;
    }

    public function fields(): array
    {
        return $this->fields;
    }

    public function meta(): array
    {
        return $this->meta;
    }
}
