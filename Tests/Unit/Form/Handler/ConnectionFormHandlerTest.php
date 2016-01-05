<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Form\Handler;

use OroCRM\Bundle\MailChimpBundle\Form\Handler\ConnectionFormHandler;

class ConnectionFormHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $form;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var ConnectionFormHandler
     */
    protected $handler;

    protected function setUp()
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new ConnectionFormHandler($this->request, $this->manager);
        $this->handler->setForm($this->form);
    }

    public function testProcessNewEntity()
    {
        $staticSegment = $this->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();
        $staticSegment->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $staticSegment->expects($this->never())
            ->method('createNewCopy');

        $this->assertParentCalls($staticSegment);
        $this->assertTrue($this->handler->process($staticSegment));
    }

    public function testProcessExistingEntity()
    {
        $staticSegment = $this->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();
        $staticSegment->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $staticSegment->expects($this->once())
            ->method('createNewCopy')
            ->will($this->returnValue($staticSegment));

        $this->assertParentCalls($staticSegment);
        $this->assertTrue($this->handler->process($staticSegment));
    }

    /**
     * @param object $entity
     */
    public function assertParentCalls($entity)
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $this->manager->expects($this->once())
            ->method('persist')
            ->with($entity);
        $this->manager->expects($this->once())
            ->method('flush');
    }
}
