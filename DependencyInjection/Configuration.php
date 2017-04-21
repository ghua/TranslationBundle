<?php
namespace VKR\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vkr_translation');
        /** @noinspection PhpUndefinedMethodInspection */
        $rootNode
            ->children()
                ->scalarNode('locale_retriever_service')
                    ->isRequired()
                ->end()
                ->scalarNode('google_api_key')
                    ->defaultNull()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
