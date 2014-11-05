<?php

namespace Aramis\Bundle\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AramisElasticsearchExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('elasticsearch', $config['elasticsearch']);
        $container->setParameter('rabbitmq', $config['rabbitmq']);

        foreach ($config['elasticsearch'] as $key => $value) {
            if ($key == 'rabbitmq_river') {
                $river = array();
                foreach ($value as $k => $v) {
                    $river[$k] = $config['elasticsearch']['rabbitmq_river'][$k];
                }
                $container->setParameter('river', $river);
            } else if ($key == 'offer_index') {
                $indexes = array();
                foreach ($value as $k => $v) {
                    $indexes[$k] = $config['elasticsearch']['offer_index'][$k];
                    $container->setParameter(
                        'elasticsearch.offer_index.'.$k,
                        $config['elasticsearch']['offer_index'][$k]
                    );
                    $container->setParameter('indexes', $indexes);
                }
            } elseif ($key == 'general_index') {
                foreach ($value as $k => $v) {
                    $container->setParameter(
                        'elasticsearch.general_index.'.$k,
                        $config['elasticsearch']['general_index'][$k]
                    );
                }
            } else {
                $container->setParameter(
                    'elasticsearch.'.$key,
                    $config['elasticsearch'][$key]
                );
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('mappings.yml');
        $loader->load('index.yml');
    }
}
