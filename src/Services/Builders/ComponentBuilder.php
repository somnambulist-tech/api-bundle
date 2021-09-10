<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Builders;

use Somnambulist\Components\Collection\MutableCollection;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use function array_map;
use function explode;
use function json_decode;
use function preg_match_all;
use function str_replace;
use const DIRECTORY_SEPARATOR;

/**
 * Class ComponentBuilder
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\Builders
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\Builders\ComponentBuilder
 */
class ComponentBuilder
{
    private array $defaults = [
        'schemas',
        'responses',
        'parameters',
        'examples',
        'requestBodies',
        'headers',
        'securitySchemes',
        'links',
        'callbacks',
    ];

    public function build(string $path): MutableCollection
    {
        $comps = $this->createCollection();
        $files = (new Finder())->files()->ignoreDotFiles(true)->in($path)->name(['*.json', '*.yaml']);

        foreach ($files as $file) {
            [$refType, $schema] = $this->getReferenceAndSchemaFromFilePath($path, $file);

            switch ($file->getExtension()) {
                case 'json':
                    $comps->get($refType)->set($schema, json_decode($this->resolveComponentReferencesInSchema($file->getContents()), true));
                    break;
                case 'yaml':
                    $comps->get($refType)->set($schema, Yaml::parse($this->resolveComponentReferencesInSchema($file->getContents())));
                    break;
            }
        }

        $comps
            ->filter(fn(MutableCollection $c) => $c->count() === 0)
            ->each(fn(MutableCollection $c, $k) => $comps->unset($k))
        ;

        return $comps;
    }

    private function createCollection(): MutableCollection
    {
        return MutableCollection::collect($this->defaults)->flip()->map(fn () => new MutableCollection());
    }

    private function getReferenceAndSchemaFromFilePath(string $path, SplFileInfo $file): array
    {
        $ref = str_replace(
            [$path . DIRECTORY_SEPARATOR, '.json', '.yaml', '\\'],
            ['', '', '', '/',],
            $file->getRealPath()
        );

        [$refType, $schema] = explode('/', $ref, 2);

        return [$refType, str_replace('/', '.', $schema)];
    }

    /**
     * Replaces slashed paths with dots as a / after the <schemas> is not allowed in the OpenAPI spec
     *
     * @param string $schema
     *
     * @return string
     */
    private function resolveComponentReferencesInSchema(string $schema): string
    {
        preg_match_all('/"#\/components\/(?<refType>[\w]+)\/(?<schema>.*)"/', $schema, $matches);

        return str_replace($matches['schema'], array_map(fn($s) => str_replace('/', '.', $s), $matches['schema']), $schema);
    }
}
