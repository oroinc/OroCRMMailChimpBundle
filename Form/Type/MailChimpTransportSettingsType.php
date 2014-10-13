<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Type;

use OroCRM\Bundle\CampaignBundle\Form\Type\AbstractTransportSettingsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MailChimpTransportSettingsType extends AbstractTransportSettingsType
{
    const NAME = 'orocrm_campaign_internal_transport_settings';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'integration',
                'orocrm_mailchimp_integration_select',
                [
                    'label' => 'orocrm.mailchimp.emailcampaign.integration.label',
                    'required' => true
                ]
            )
            ->add(
                'template',
                'orocrm_mailchimp_template_select',
                [
                    'label' => 'orocrm.mailchimp.emailcampaign.template.label',
                    'required' => true,
                    /*
                    'depends_on_parent_field' => 'marketingList',
                    'data_route' => 'orocrm_api_get_emailcampaign_email_templates',
                    'data_route_parameter' => 'id'
                    */
                ]
            );

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
        return self::NAME;
    }
}
