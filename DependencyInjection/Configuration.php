<?php

namespace Oro\Bundle\MailChimpBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const STATIC_SEGMENT_SYNC_MODE_ON_UPDATE = 'on_update';
    const STATIC_SEGMENT_SYNC_MODE_SCHEDULED = 'scheduled';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_mailchimp');

        SettingsBuilder::append(
            $rootNode,
            [
                'static_segment_sync_mode' => ['value' => self::STATIC_SEGMENT_SYNC_MODE_ON_UPDATE],
            ]
        );

        return $treeBuilder;
    }
}
