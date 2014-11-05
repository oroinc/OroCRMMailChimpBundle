<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Validator;

use OroCRM\Bundle\MailChimpBundle\Validator\EmailColumnValidator;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;

class EmailColumnValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldInformationValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
        $context = $this->getMockForAbstractClass('Symfony\Component\Validator\ExecutionContextInterface');

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
        $manualMarketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
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
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
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
            ->with('OroCRMMailChimpBundle:StaticSegment')
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

        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
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
            ->with('OroCRMMailChimpBundle:StaticSegment')
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
