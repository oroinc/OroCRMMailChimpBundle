<?php

namespace OroCRM\Bundle\MailChimpBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class EmailColumnConstraint extends Constraint
{
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
        return 'orocrm_mailchimp.validator.email_column';
    }
}
