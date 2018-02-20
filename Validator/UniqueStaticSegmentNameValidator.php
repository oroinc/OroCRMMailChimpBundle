<?php

namespace Oro\Bundle\MailChimpBundle\Validator;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;
use Oro\Bundle\MailChimpBundle\Validator\Constraints\UniqueStaticSegmentNameConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
        if ($value instanceof StaticSegment && !$value->getOriginId()) {
            $this->transport->init($value->getChannel()->getTransport());

            $segments = $this->transport->getListStaticSegments($value->getSubscribersList());
            foreach ($segments as $segment) {
                if ($segment['name'] === $value->getName()) {
                    $this->context->addViolationAt('name', $constraint->message);
                    break;
                }
            }
        }
    }
}
