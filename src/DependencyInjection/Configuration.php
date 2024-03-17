<?php



/*
 * This file is part of the SymfonyCasts ResetPasswordBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DoctrineEncryptor\DoctrineEncryptorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author xorg <xorg@i2p.i2p>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('doctrine_encryptor');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('encryptor_off')->defaultFalse()->end()
                ->scalarNode('encryptor_cipher_algorithm')->defaultValue("Camellia-256-CBC")->end()
                ->scalarNode('encryptor_system')->defaultValue("halite")->end()
                ->scalarNode('encryptor_storage')->defaultValue(null)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}