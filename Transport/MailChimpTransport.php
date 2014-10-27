<?php

namespace OroCRM\Bundle\MailChimpBundle\Transport;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Transport\VisibilityTransportInterface;
use OroCRM\Bundle\MailChimpBundle\Form\Type\MailChimpTransportSettingsType;

class MailChimpTransport implements VisibilityTransportInterface
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
     * Determination of transport options in the form of creation.
     *
     * @return bool
     */
    public function isVisibleInForm()
    {
        return false;
    }
}
