<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

class DigitsBetweenConverter extends BetweenConverter
{
    public function rule(): string
    {
        return 'digits_between';
    }
}
