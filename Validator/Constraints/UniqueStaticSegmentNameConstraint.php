<?php

namespace OroCRM\Bundle\MailChimpBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueStaticSegmentNameConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'orocrm.mailchimp.validator.unique_static_segment_name.message';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT];
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_mailchimp.validator.unique_static_segment_name';
    }
}
