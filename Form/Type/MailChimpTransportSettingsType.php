<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\CampaignBundle\Form\Type\AbstractTransportSettingsType;

class MailChimpTransportSettingsType extends AbstractTransportSettingsType
{
    const NAME = 'orocrm_mailchimp_email_transport_settings';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'channel',
                'orocrm_mailchimp_integration_select',
                [
                    'label' => 'orocrm.mailchimp.emailcampaign.integration.label',
                    'required' => true
                ]
            )
            /*
            ->add(
                'template',
                'orocrm_mailchimp_template_select',
                [
                    'label' => 'orocrm.mailchimp.emailcampaign.template.label',
                    'required' => true,
                    'channel_field' => 'channel'
                ]
            )*/;

        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
