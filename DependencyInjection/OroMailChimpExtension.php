<?php

namespace Oro\Bundle\MailChimpBundle\DependencyInjection;

use Oro\Bundle\MailChimpBundle\Async\ExportMailChimpProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OroMailChimpExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $messageQueueConfigs = $container->getExtensionConfig('oro_message_queue');
        foreach ($messageQueueConfigs as $messageQueueConfig) {
            if (isset($messageQueueConfig['time_before_stale'])
                && array_key_exists('jobs', $messageQueueConfig['time_before_stale'])
            ) {
                $config['time_before_stale']['jobs'][ExportMailChimpProcessor::JOB_NAME_PREFIX]
                    = ExportMailChimpProcessor::JOB_TIME_BEFORE_STALE;
                $container->prependExtensionConfig('oro_message_queue', $config);
            }
        }
    }
}
