<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use DateTime;
use function array_merge;
use function preg_match;
use function preg_replace;

class DateConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'date';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        $params  = $params ?: 'Y-m-d';
        $hasTime = preg_match('/[aABgGhHisuveIOPpTZcrU]/', preg_replace('/\\\\./', '', $params));

        return array_merge($schema, [
            'title'   => $params,
            'format'  => $hasTime ? 'date-time' : 'date',
            'example' => (new DateTime('2000-01-02T03:04:05.006-07:00'))->format($params),
        ]);
    }
}
