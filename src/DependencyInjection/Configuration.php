<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @package Somnambulist\ApiBundle\DependencyInjection
 * @subpackage Somnambulist\ApiBundle\DependencyInjection\Configuration
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
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
                    ->end()
                ->end()
                ->arrayNode('subscribers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('exception_to_json')->defaultTrue()->end()
                        ->booleanNode('json_to_post')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
