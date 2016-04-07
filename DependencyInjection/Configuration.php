<?php

/**
 * Copyright (c) 2015 Ben Zörb
 * Licensed under the MIT license.
 * http://bezoerb.mit-license.org/
 */

namespace Zoerb\Bundle\FilerevBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zoerb_filerev');

        $organizeUrls = function ($urls) {
            $urls += array(
                'http' => array(),
                'ssl' => array(),
            );

            foreach ($urls as $i => $url) {
                if (is_int($i)) {
                    if (0 === strpos($url, 'https://') || 0 === strpos($url, '//')) {
                        $urls['http'][] = $urls['ssl'][] = $url;
                    } else {
                        $urls['http'][] = $url;
                    }
                    unset($urls[$i]);
                }
            }

            return $urls;
        };

        $rootNode
            ->info('Filerev configuration')
            ->canBeDisabled()
            ->children()
                ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                ->scalarNode('root_dir')->defaultValue('%kernel.root_dir%/../web')->end()
                ->scalarNode('length')->defaultValue(8)->end()
                ->scalarNode('summary_file')->defaultValue('%kernel.root_dir%/config/filerev.json')->end()
                ->scalarNode('base_path')->end()
                ->arrayNode('base_urls')
                    ->beforeNormalization()
                        ->ifTrue(function ($v) { return !is_array($v); })
                        ->then(function ($v) { return array($v); })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('packages')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('base_url')
                        ->children()
                            ->scalarNode('version')
                                ->beforeNormalization()
                                ->ifTrue(function ($v) { return '' === $v; })
                                ->then(function ($v) { return; })
                                ->end()
                            ->end()
                            ->scalarNode('version_format')->defaultNull()->end()
                            ->scalarNode('base_path')->defaultValue('')->end()
                            ->arrayNode('base_urls')
                                // ->requiresAtLeastOneElement()
                                ->beforeNormalization()
                                    ->ifTrue(function ($v) { return !is_array($v); })
                                    ->then(function ($v) { return array($v); })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
