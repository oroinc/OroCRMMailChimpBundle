<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Validator;

use Oro\Bundle\MailChimpBundle\Validator\EmailColumnValidator;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use Oro\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EmailColumnValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $fieldInformationValidator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var EmailColumnValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->fieldInformationValidator = $this->getMockBuilder('Symfony\Component\Validator\ConstraintValidator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = new EmailColumnValidator($this->fieldInformationValidator, $this->registry);
    }

    public function testInitialize()
    {
        $context = $this->getMockForAbstractClass(ExecutionContextInterface::class);

        $this->fieldInformationValidator->expects($this->once())
            ->method('initialize')
            ->with($context);

        $this->validator->initialize($context);
    }

    /**
     * @dataProvider validDataProvider
     * @param mixed $value
     */
    public function testValidateValid($value)
    {
        $this->fieldInformationValidator->expects($this->never())
            ->method('validate');

        $constraint = $this->getMockBuilder('Symfony\Component\Validator\Constraint')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator->validate($value, $constraint);
    }

    /**
     * @return array
     */
    public function validDataProvider()
    {
        $manualMarketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $manualMarketingList->expects($this->any())
            ->method('isManual')
            ->will($this->returnValue(true));

        return [
            [new \stdClass()],
            [$manualMarketingList]
        ];
    }

    public function testValidateNotConnected()
    {
        $marketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(false));

        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['marketingList' => $marketingList]);
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroMailChimpBundle:StaticSegment')
            ->will($this->returnValue($repository));

        $constraint = $this->getMockBuilder('Symfony\Component\Validator\Constraint')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldInformationValidator->expects($this->never())
            ->method('validate');

        $this->validator->validate($marketingList, $constraint);
    }

    public function testValidate()
    {
        $segment = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')
            ->disableOriginalConstructor()
            ->getMock();

        $marketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(false));
        $marketingList->expects($this->once())
            ->method('getSegment')
            ->will($this->returnValue($segment));

        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['marketingList' => $marketingList])
            ->will($this->returnValue(new \stdClass()));
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroMailChimpBundle:StaticSegment')
            ->will($this->returnValue($repository));

        $fieldValidatorConstraint = new ContactInformationColumnConstraint();
        $fieldValidatorConstraint->type = ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL;

        $constraint = $this->getMockBuilder('Symfony\Component\Validator\Constraint')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldInformationValidator->expects($this->once())
            ->method('validate')
            ->with($segment, $fieldValidatorConstraint);

        $this->validator->validate($marketingList, $constraint);
    }
}
