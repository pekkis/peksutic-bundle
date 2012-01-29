<?php

namespace Pekkis\PeksuticBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;


/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PekkisPeksuticExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $assetizer = $container->getDefinition('pekkis_peksutic.service.assetizer');

        foreach ($config['collections'] as $collection) {
            $assetizer->addMethodCall('addCollection', array($collection));
        }

        foreach ($config['parsers'] as $collection) {
            $assetizer->addMethodCall('addParser', array($collection));
        }
        
        $twig = $container->getDefinition('pekkis_peksutic.twig.extension');
        $twig->addArgument($config['base_url']);
        
    }
}
