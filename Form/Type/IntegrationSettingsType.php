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
                'text',
                [
                    'label' => 'orocrm.mailchimp.integration_transport.api_key.label',
                    'tooltip' => 'orocrm.mailchimp.form.api_key.tooltip',
                    'required' => true
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
