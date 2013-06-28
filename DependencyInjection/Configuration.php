<?php

namespace KMJ\SyncBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kmj_sync');

        $rootNode->children()
                    ->scalarNode('dir')
                        ->defaultValue('%kernel.root_dir%/cache/syncing')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('backups')
                        ->defaultValue('%kernel.root_dir%/Resources/backups')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('numberofbackups')
                        ->defaultValue('3')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('compression')
                        ->defaultValue('tar')
                        ->validate()
                            ->ifNotInArray(array('tar'))
                            ->thenInvalid('Invalid compression type of "%s"')
                        ->end()
                    ->end()
                    ->arrayNode('paths')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('path')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('ssh')
                        ->children()
                            ->scalarNode('host')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('port')
                                ->cannotBeEmpty()
                                ->defaultValue('22')
                            ->end()
                            ->scalarNode('username')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('path')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('database')
                        ->children()
                            ->scalarNode('type')
                                ->defaultValue('mysql')
                                ->validate()
                                    ->ifNotInArray(array('mysql'))
                                    ->thenInvalid('Invalid type "%s"')
                                ->end()
                            ->end()
                            ->scalarNode('host')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('database')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('user')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                             ->scalarNode('password')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }

}
