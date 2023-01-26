<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Request\Filters;

use function array_filter;
use function explode;
use function substr;
use function trim;

class ConvertOrderByToArray
{
    public function __invoke(string $string): array
    {
        $fields = [];

        foreach (array_filter(explode(',', $string)) as $field) {
            $dir = 'asc';

            if (str_contains($field, ':')) {
                [$field, $dir] = explode(':', $field);

                if (!in_array($dir, ['asc', 'desc'])) {
                    $dir = 'asc';
                }
            }

            if (str_starts_with(trim($field), '-')) {
                $field = substr(trim($field), 1);
                $dir = 'desc';
            }

            $fields[trim($field)] = mb_strtoupper(trim($dir));
        }

        return $fields;
    }
}
