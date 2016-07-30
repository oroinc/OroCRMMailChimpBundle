<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MailchimpTemplateSelectType extends AbstractType
{
    const NAME = 'orocrm_mailchimp_template_select';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'mailchimp_templates',
                'grid_name' => 'orocrm_mailchimp_templates_grid',
                'configs' => [
                    'placeholder' => 'orocrm.mailchimp.emailcampaign.template.placeholder'
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
        return 'oro_entity_create_or_select_inline_channel_aware';
    }
}
