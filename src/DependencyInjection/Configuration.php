<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @package    Somnambulist\Bundles\ApiBundle\DependencyInjection
 * @subpackage Somnambulist\Bundles\ApiBundle\DependencyInjection\Configuration
 */
class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('somnambulist_api');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('exception_handler')
                    ->children()
                        ->arrayNode('converters')
                            ->useAttributeAsKey('exception')->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('request_handler')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('per_page')->defaultValue(20)->end()
                        ->integerNode('max_per_page')->defaultValue(100)->end()
                        ->integerNode('limit')->defaultValue(100)->end()
                        ->scalarNode('request_id_header')->defaultValue('X-Request-Id')->end()
                    ->end()
                ->end()
                ->arrayNode('subscribers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('exception_to_json')->defaultTrue()->end()
                        ->booleanNode('json_to_post')->defaultTrue()->end()
                        ->booleanNode('request_id')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('openapi')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultValue('%kernel.project_dir%/config/openapi')->end()
                        ->scalarNode('title')->defaultValue('API Documentation')->end()
                        ->scalarNode('version')->defaultValue('1.0.0')->end()
                        ->scalarNode('description')->defaultValue('Auto-generated API documentation for the service')->end()
                        ->scalarNode('cache_time')->defaultValue('43200')->end()
                        ->arrayNode('tags')
                            ->useAttributeAsKey('tag')->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('security')
                            ->useAttributeAsKey('scheme')->variablePrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
