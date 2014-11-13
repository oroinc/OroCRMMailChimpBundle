<?php

namespace OroCRM\Bundle\MailChimpBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;
use OroCRM\Bundle\MailChimpBundle\Validator\Constraints\UniqueStaticSegmentNameConstraint;

class UniqueStaticSegmentNameValidator extends ConstraintValidator
{
    /**
     * @var TransportInterface|MailChimpTransport
     */
    protected $transport;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param StaticSegment $value
     * @param UniqueStaticSegmentNameConstraint|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof StaticSegment) {
            $this->transport->init($value->getChannel()->getTransport());

            $segments = $this->transport->getListStaticSegments($value->getSubscribersList());
            foreach ($segments as $segment) {
                if ($segment['name'] == $value->getName()) {
                    $this->context->addViolationAt('name', $constraint->message);
                    break;
                }
            }
        }
    }
}
