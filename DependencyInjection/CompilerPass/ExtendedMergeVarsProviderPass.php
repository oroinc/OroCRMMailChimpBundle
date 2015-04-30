<?php

namespace OroCRM\Bundle\MailChimpBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ExtendedMergeVarsProviderPass implements CompilerPassInterface
{
    const COMPOSITE_PROVIDER_ID = 'orocrm_mailchimp.extended_merge_var.composite_provider';
    const PROVIDER_TAG_NAME     = 'orocrm_mailchimp.extended_merge_vars.provider';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $compositeProvider = $container->getDefinition(self::COMPOSITE_PROVIDER_ID);
        $providers = $container->findTaggedServiceIds(self::PROVIDER_TAG_NAME);

        foreach ($providers as $serviceId => $tags) {
            $ref = new Reference($serviceId);
            $compositeProvider->addMethodCall('addProvider', [$ref]);
        }
    }
}
