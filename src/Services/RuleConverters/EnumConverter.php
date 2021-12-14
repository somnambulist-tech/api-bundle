<?php
/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use BackedEnum;
use Eloquent\Enumeration\AbstractMultiton;
use Eloquent\Enumeration\AbstractValueMultiton;
use UnitEnum;
use function array_filter;
use function array_map;
use function array_values;
use function explode;
use function is_subclass_of;

/**
 * Class EnumConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\EnumConverter
 */
class EnumConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'enum';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, [
            'enum' => (
                $this->nativeEnum($params) ??
                $this->eloquentEnum($params) ??
                $this->csvString($params)
            ),
        ]);
    }

    /**
     * @param string $class
     * @return ?string[]
     */
    protected function nativeEnum(string $class): ?array
    {
        if (PHP_VERSION_ID < 80100 || !is_subclass_of($class, UnitEnum::class)) {
            return null;
        }

        $callback = is_subclass_of($class, BackedEnum::class) ?
            fn (BackedEnum $enum) => $enum->value :
            fn (UnitEnum $enum)   => $enum->name;

        return array_map($callback, $class::cases());
    }

    /**
     * @param string $class
     * @return ?string[]
     */
    protected function eloquentEnum(string $class): ?array
    {
        if (!class_exists(AbstractMultiton::class) || !is_subclass_of($class, AbstractMultiton::class)) {
            return null;
        }

        $callback = is_subclass_of($class, AbstractValueMultiton::class) ?
            fn (AbstractValueMultiton $enum) => $enum->value() :
            fn (AbstractMultiton $enum)      => $enum->key();

        return array_map($callback, array_values($class::members()));
    }

    /**
     * @param string $csv
     * @return string[]
     */
    protected function csvString(string $csv): array
    {
        return strlen(trim($csv)) ? array_values(array_filter(array_map('trim', explode(',', $csv)), 'strlen')) : [];
    }
}