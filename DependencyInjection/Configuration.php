<?php

namespace Pekkis\PeksuticBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pekkis_peksutic');

        $this->addGenericNode($rootNode);
        
        $this->addCollectionsNode($rootNode);
        
        $this->addParserNode($rootNode);
        
        
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
    
    
    public function addGenericNode(ArrayNodeDefinition $rootNode)
    {
        $rootNode->children()
            ->scalarNode('base_url')->end()
        ->end();
    }
    
    
    public function addCollectionsNode(ArrayNodeDefinition $rootNode)
    {
        $rootNode->children()
            ->arrayNode('collections')
                ->prototype('array')
                    ->children()
                        ->arrayNode('write')
                            ->children()
                                ->booleanNode('combined')->end()
                                ->booleanNode('leaves')->end()
                             ->end()
                        ->end()
                        ->arrayNode('options')
                            ->children()
                                ->booleanNode('debug')->end()
                                ->scalarNode('name')->end()
                                ->scalarNode('output')->end()
                            ->end()
                        ->end()
                        ->arrayNode('filters')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('inputs')
                            ->prototype('scalar')->end()
                        ->end()
                        
                
                    ->end()
            ->end()
        ->end();

    }
    
    
    public function addParserNode(ArrayNodeDefinition $rootNode)
    {
        $rootNode->children()
            ->arrayNode('parsers')
                 ->prototype('array')
                 ->children()
                     ->scalarNode('debug')->end()
                     ->scalarNode('directory')->end()
                     ->arrayNode('blacklist')
                            ->prototype('scalar')->end()
                     ->end()
                     ->arrayNode('files')
                            ->prototype('array')
                            ->children()
                                ->scalarNode('pattern')->end()
                                ->arrayNode('filters')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('output')->end()
                            ->end()
                     ->end()
                 ->end()
            ->end();
    }
    
    
    
}
