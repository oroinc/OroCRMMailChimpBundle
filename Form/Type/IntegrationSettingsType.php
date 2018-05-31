<?php

namespace Oro\Bundle\MailChimpBundle\Form\Type;

use Oro\Bundle\MailChimpBundle\Form\Type\ApiKeyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                ApiKeyType::class,
                [
                    'label' => 'oro.mailchimp.integration_transport.api_key.label',
                    'tooltip' => 'oro.mailchimp.form.api_key.tooltip',
                    'required' => true
                ]
            )
            ->add(
                'activityUpdateInterval',
                ChoiceType::class,
                [
                    'label' => 'oro.mailchimp.integration_transport.activity_update_interval.label',
                    'tooltip' => 'oro.mailchimp.form.activity_update_interval.tooltip',
                    'choices' => [
                        'oro.mailchimp.integration_transport.activity_update_interval.choice.forever' => '0',
                        'oro.mailchimp.integration_transport.activity_update_interval.choice.1week' => '7',
                        'oro.mailchimp.integration_transport.activity_update_interval.choice.2week' => '14',
                        'oro.mailchimp.integration_transport.activity_update_interval.choice.1month' => '30',
                        'oro.mailchimp.integration_transport.activity_update_interval.choice.2month' => '60',
                        'oro.mailchimp.integration_transport.activity_update_interval.choice.3month' => '90',
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
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
