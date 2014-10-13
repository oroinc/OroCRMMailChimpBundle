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
                'class' => 'OroCRM\Bundle\MailChimpBundle\Entity\Template',
                'property' => 'name',
                'group_by' => 'category'
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

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_entity';
    }
}
