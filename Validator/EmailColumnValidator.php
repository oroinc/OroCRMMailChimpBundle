<?php

namespace Oro\Bundle\MailChimpBundle\Validator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use Oro\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EmailColumnValidator extends ConstraintValidator
{
    /**
     * @var ConstraintValidator
     */
    protected $fieldInformationValidator;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ConstraintValidator $fieldInformationValidator
     * @param ManagerRegistry $registry
     */
    public function __construct(ConstraintValidator $fieldInformationValidator, ManagerRegistry $registry)
    {
        $this->fieldInformationValidator = $fieldInformationValidator;
        $this->registry = $registry;
    }

    public function initialize(ExecutionContextInterface $context)
    {
        $this->fieldInformationValidator->initialize($context);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof MarketingList && !$value->isManual() && $this->isConnectedToMailChimp($value)) {
            $fieldValidatorConstraint = new ContactInformationColumnConstraint();
            $fieldValidatorConstraint->type = ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL;
            $this->fieldInformationValidator->validate($value->getSegment(), $fieldValidatorConstraint);
        }
    }

    /**
     * @param MarketingList $marketingList
     * @return bool
     */
    protected function isConnectedToMailChimp(MarketingList $marketingList)
    {
        return (bool)$this->registry->getRepository('OroMailChimpBundle:StaticSegment')
            ->findOneBy(['marketingList' => $marketingList]);
    }
}
