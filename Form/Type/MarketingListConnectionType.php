<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketingListConnectionType extends AbstractType
{
    const NAME = 'orocrm_mailchimp_marketing_list_connection';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'label' => 'orocrm.mailchimp.connection.segment_name',
                    'required' => true
                ]
            )
            ->add(
                'channel',
                'orocrm_mailchimp_integration_select',
                [
                    'label' => 'orocrm.mailchimp.emailcampaign.integration.label',
                    'required' => true
                ]
            )
            ->add(
                'subscribersList',
                'orocrm_mailchimp_list_select',
                [
                    'label' => 'orocrm.mailchimp.subscriberslist.entity_label',
                    'required' => true,
                    'channel_field' => 'channel'
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment'
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
