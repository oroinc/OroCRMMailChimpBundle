<?php

namespace OroCRM\Bundle\MailChimpBundle\Transport;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Transport\TransportInterface;
use Oro\Bundle\CampaignBundle\Transport\VisibilityTransportInterface;
use OroCRM\Bundle\MailChimpBundle\Form\Type\MailChimpTransportSettingsType;

class MailChimpTransport implements TransportInterface, VisibilityTransportInterface
{
    const NAME = 'mailchimp';

    /**
     * {@inheritdoc}
     */
    public function send(EmailCampaign $campaign, $entity, array $from, array $to)
    {
        // TODO: Implement send CRM-1980
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.mailchimp.emailcampaign.transport.' . self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return MailChimpTransportSettingsType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings';
    }

    /**
     * {@inheritdoc}
     */
    public function isVisibleInForm()
    {
        return false;
    }
}
