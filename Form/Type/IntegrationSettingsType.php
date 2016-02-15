<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IntegrationSettingsType extends AbstractType
{
    const NAME = 'orocrm_mailchimp_integration_transport_setting_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'apiKey',
                'orocrm_mailchimp_api_key_type',
                [
                    'label' => 'orocrm.mailchimp.integration_transport.api_key.label',
                    'tooltip' => 'orocrm.mailchimp.form.api_key.tooltip',
                    'required' => true
                ]
            )
            ->add(
                'activityUpdateInterval',
                'choice',
                [
                    'label' => 'orocrm.mailchimp.integration_transport.activity_update_interval.label',
                    'tooltip' => 'orocrm.mailchimp.form.activity_update_interval.tooltip',
                    'choices' => [
                        '0' => 'orocrm.mailchimp.integration_transport.activity_update_interval.choice.forever',
                        '7' => 'orocrm.mailchimp.integration_transport.activity_update_interval.choice.1week',
                        '14' => 'orocrm.mailchimp.integration_transport.activity_update_interval.choice.2week',
                        '30' => 'orocrm.mailchimp.integration_transport.activity_update_interval.choice.1month',
                        '60' => 'orocrm.mailchimp.integration_transport.activity_update_interval.choice.2month',
                        '90' => 'orocrm.mailchimp.integration_transport.activity_update_interval.choice.3month'
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransport']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
