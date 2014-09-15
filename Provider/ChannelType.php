<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class ChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'mailchimp';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.mailchimp.channel_type.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/orocrmmailchimp/img/freddie.ico';
    }
}
