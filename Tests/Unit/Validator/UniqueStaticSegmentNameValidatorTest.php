<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Validator;

use Oro\Bundle\MailChimpBundle\Validator\Constraints\UniqueStaticSegmentNameConstraint;
use Oro\Bundle\MailChimpBundle\Validator\UniqueStaticSegmentNameValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueStaticSegmentNameValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transport;

    /**
     * @var UniqueStaticSegmentNameValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->transport = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new UniqueStaticSegmentNameValidator($this->transport);
    }

    public function testValidateIncorrectInstance()
    {
        $value = new \stdClass();
        $constraint = new UniqueStaticSegmentNameConstraint();

        $this->transport->expects($this->never())
            ->method($this->anything());

        $this->validator->validate($value, $constraint);
    }

    public function testValidateHasOrigin()
    {
        $value = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();
        $value->expects($this->once())
            ->method('getOriginId')
            ->will($this->returnValue('123'));
        $constraint = new UniqueStaticSegmentNameConstraint();

        $this->transport->expects($this->never())
            ->method($this->anything());

        $this->validator->validate($value, $constraint);
    }

    public function testValidateCorrect()
    {
        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->disableOriginalConstructor()
            ->getMock();

        $channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        $list = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\SubscribersList')
            ->disableOriginalConstructor()
            ->getMock();

        $value = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();
        $value->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($channel));
        $value->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('other'));
        $value->expects($this->once())
            ->method('getSubscribersList')
            ->will($this->returnValue($list));
        $constraint = new UniqueStaticSegmentNameConstraint();

        $this->transport->expects($this->once())
            ->method('init')
            ->with($transport);
        $this->transport->expects($this->once())
            ->method('getListStaticSegments')
            ->with($list)
            ->will($this->returnValue([['name' => 'some']]));

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())
            ->method($this->anything());

        $this->validator->initialize($context);
        $this->validator->validate($value, $constraint);
    }

    public function testValidateIncorrect()
    {
        $name = 'other';

        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->disableOriginalConstructor()
            ->getMock();

        $channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        $list = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\SubscribersList')
            ->disableOriginalConstructor()
            ->getMock();

        $value = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();
        $value->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($channel));
        $value->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));
        $value->expects($this->once())
            ->method('getSubscribersList')
            ->will($this->returnValue($list));
        $constraint = new UniqueStaticSegmentNameConstraint();

        $this->transport->expects($this->once())
            ->method('init')
            ->with($transport);
        $this->transport->expects($this->once())
            ->method('getListStaticSegments')
            ->with($list)
            ->will($this->returnValue([['name' => $name]]));

        $context = $this->createMock(ExecutionContextInterface::class);
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($builder);
        $builder->expects($this->once())
            ->method('atPath')
            ->with('name')
            ->willReturnSelf();
        $builder->expects($this->once())
            ->method('addViolation');

        $this->validator->initialize($context);
        $this->validator->validate($value, $constraint);
    }
}
