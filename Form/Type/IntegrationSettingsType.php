<?php

namespace Oro\Bundle\MailChimpBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Mailchimp integration settings form type.
 */
class IntegrationSettingsType extends AbstractType
{
    const NAME = 'oro_mailchimp_integration_transport_setting_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'apiKey',
                'oro_mailchimp_api_key_type',
                [
                    'label' => 'oro.mailchimp.integration_transport.api_key.label',
                    'tooltip' => 'oro.mailchimp.form.api_key.tooltip',
                    'required' => true,
                    'attr' => ['autocomplete' => 'off'],
                ]
            )
            ->add(
                'activityUpdateInterval',
                'choice',
                [
                    'label' => 'oro.mailchimp.integration_transport.activity_update_interval.label',
                    'tooltip' => 'oro.mailchimp.form.activity_update_interval.tooltip',
                    'choices' => [
                        '0' => 'oro.mailchimp.integration_transport.activity_update_interval.choice.forever',
                        '7' => 'oro.mailchimp.integration_transport.activity_update_interval.choice.1week',
                        '14' => 'oro.mailchimp.integration_transport.activity_update_interval.choice.2week',
                        '30' => 'oro.mailchimp.integration_transport.activity_update_interval.choice.1month',
                        '60' => 'oro.mailchimp.integration_transport.activity_update_interval.choice.2month',
                        '90' => 'oro.mailchimp.integration_transport.activity_update_interval.choice.3month'
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Oro\Bundle\MailChimpBundle\Entity\MailChimpTransport']);
    }

    /**
     * {@inheritdoc}
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
