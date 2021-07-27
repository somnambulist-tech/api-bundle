<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_merge;

/**
 * Class UploadedFileConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\UploadedFileConverter
 */
class UploadedFileConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'uploaded_file';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, [
            'title'  => 'Uploaded File',
            'format' => 'binary',
        ]);
    }
}
