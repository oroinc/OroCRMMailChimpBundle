<?php

namespace Oro\Bundle\MailChimpBundle\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\CreateOrSelectInlineChannelAwareType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailchimpListSelectType extends AbstractType
{
    const NAME = 'oro_mailchimp_list_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'mailchimp_lists',
                'grid_name' => 'oro_mailchimp_lists_grid',
                'configs' => [
                    'placeholder' => 'oro.mailchimp.emailcampaign.list.placeholder'
                ]
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

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CreateOrSelectInlineChannelAwareType::class;
    }
}
