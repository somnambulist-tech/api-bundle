<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\Behaviours;

use function explode;
use function str_contains;
use function str_getcsv;

trait ConvertStringToArray
{
    protected function shouldBeArray(string $value, string $separator = ','): bool
    {
        return str_contains($value, $separator);
    }

    protected function convertToArray(string $value, string $separator = ','): array
    {
        return str_getcsv($value, $separator);
    }
}
