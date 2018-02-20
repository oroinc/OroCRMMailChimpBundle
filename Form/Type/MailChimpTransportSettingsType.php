<?php

namespace Oro\Bundle\MailChimpBundle\Form\Type;

use Oro\Bundle\CampaignBundle\Form\Type\AbstractTransportSettingsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailChimpTransportSettingsType extends AbstractTransportSettingsType
{
    const NAME = 'oro_mailchimp_email_transport_settings';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'channel',
                'oro_mailchimp_integration_select',
                [
                    'label' => 'oro.mailchimp.emailcampaign.integration.label',
                    'required' => true
                ]
            )
            /*
            ->add(
                'template',
                'oro_mailchimp_template_select',
                [
                    'label' => 'oro.mailchimp.emailcampaign.template.label',
                    'required' => true,
                    'channel_field' => 'channel'
                ]
            )*/;

        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Oro\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings'
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
