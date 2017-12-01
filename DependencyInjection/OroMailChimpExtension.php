<?php

namespace Oro\Bundle\MailChimpBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OroMailChimpExtension extends Extension
{
    const ALIAS = 'oro_mailchimp';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }
}
