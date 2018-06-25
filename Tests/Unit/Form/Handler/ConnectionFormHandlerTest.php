<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\MailChimpBundle\Entity\Campaign;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Form\Handler\ConnectionFormHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ConnectionFormHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FormInterface
     */
    protected $form;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Request
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    protected $registry;

    /**
     * @var ConnectionFormHandler
     */
    protected $handler;

    protected function setUp()
    {
        $this->form = $this->createMock('Symfony\Component\Form\FormInterface');

        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->handler = new ConnectionFormHandler($this->request, $this->registry, $this->form);
    }

    public function testProcessGet()
    {
        /** @var StaticSegment|\PHPUnit\Framework\MockObject\MockObject $staticSegment */
        $staticSegment = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();
        $staticSegment->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        $staticSegmentManager = $this->createMock('\Doctrine\Common\Persistence\ObjectManager');
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('OroMailChimpBundle:StaticSegment');

        $staticSegmentManager->expects($this->never())
            ->method($this->anything());
        $this->request->expects($this->once())
            ->method('isMethod')
            ->with('POST')
            ->will($this->returnValue(false));

        $this->assertNull($this->handler->process($staticSegment));
    }

    public function testProcessNewEntity()
    {
        /** @var StaticSegment|\PHPUnit\Framework\MockObject\MockObject $staticSegment */
        $staticSegment = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();
        $staticSegment->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        $staticSegmentManager = $this->createMock('\Doctrine\Common\Persistence\ObjectManager');
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('OroMailChimpBundle:StaticSegment')
            ->will($this->returnValue($staticSegmentManager));

        $this->assertSubmit();
        $this->assertSave($staticSegmentManager, $staticSegment);

        $this->assertSame($staticSegment, $this->handler->process($staticSegment));
    }

    public function testProcessExistingEntitySameList()
    {
        $subscribersList = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\SubscribersList')
            ->disableOriginalConstructor()
            ->getMock();
        $subscribersList->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));

        /** @var StaticSegment|\PHPUnit\Framework\MockObject\MockObject $staticSegment */
        $staticSegment = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();
        $staticSegment->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $staticSegment->expects($this->any())
            ->method('getSubscribersList')
            ->will($this->returnValue($subscribersList));
        $staticSegment->expects($this->once())
            ->method('setSyncStatus')
            ->with(StaticSegment::STATUS_SCHEDULED);

        $staticSegmentManager = $this->createMock('\Doctrine\Common\Persistence\ObjectManager');
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('OroMailChimpBundle:StaticSegment')
            ->will($this->returnValue($staticSegmentManager));

        $this->assertSubmit();
        $this->assertSave($staticSegmentManager, $staticSegment);

        $this->assertSame($staticSegment, $this->handler->process($staticSegment));
    }

    /**
     * @dataProvider campaignDataProvider
     * @param Campaign|null $campaign
     */
    public function testProcessExistingEntityListChange($campaign)
    {
        $subscribersList = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\SubscribersList')
            ->disableOriginalConstructor()
            ->getMock();
        $subscribersList->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));

        /** @var StaticSegment|\PHPUnit\Framework\MockObject\MockObject $staticSegment */
        $staticSegment = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $staticSegment->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $staticSegment->setSubscribersList($subscribersList);

        $staticSegmentManager = $this->createMock('\Doctrine\Common\Persistence\ObjectManager');
        $staticSegmentManager->expects(!$campaign ? $this->once() : $this->never())
            ->method('remove');

        $campaignManager = $this->createMock('\Doctrine\Common\Persistence\ObjectManager');
        $campaignRepository = $this->createMock('\Doctrine\Common\Persistence\ObjectRepository');
        $campaignRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($campaign));
        $campaignManager->expects($this->any())
            ->method('getRepository')
            ->with('OroMailChimpBundle:Campaign')
            ->will($this->returnValue($campaignRepository));

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->will(
                $this->returnValueMap(
                    [
                        ['OroMailChimpBundle:StaticSegment', $staticSegmentManager],
                        ['OroMailChimpBundle:Campaign', $campaignManager]
                    ]
                )
            );

        $this->request->expects($this->once())
            ->method('isMethod')
            ->with(Request::METHOD_POST)
            ->will($this->returnValue(true));

        $this->form->expects($this->once())
            ->method('handleRequest')
            ->with($this->request)
            ->will(
                $this->returnCallback(
                    function () use ($staticSegment) {
                        $subscribersList = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Entity\SubscribersList')
                            ->disableOriginalConstructor()
                            ->setMethods(['getId'])
                            ->getMock();
                        $subscribersList->expects($this->any())
                            ->method('getId')
                            ->will($this->returnValue(1));
                        $staticSegment->setSubscribersList($subscribersList);
                    }
                )
            );
        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $staticSegmentManager->expects($this->once())
            ->method('persist');
        $staticSegmentManager->expects($this->once())
            ->method('flush');

        $actualSegment = $this->handler->process($staticSegment);
        $this->assertInstanceOf('Oro\Bundle\MailChimpBundle\Entity\StaticSegment', $actualSegment);
        $this->assertNull($actualSegment->getId());
        $this->assertEquals(StaticSegment::STATUS_NOT_SYNCED, $actualSegment->getSyncStatus());
    }

    /**
     * @return array
     */
    public function campaignDataProvider()
    {
        return [
            [new Campaign()],
            [null]
        ];
    }

    protected function assertSubmit()
    {
        $this->request->expects($this->once())
            ->method('isMethod')
            ->with(Request::METHOD_POST)
            ->will($this->returnValue(true));
        $this->form->expects($this->once())
            ->method('handleRequest')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $staticSegmentManager
     * @param StaticSegment $staticSegment
     */
    protected function assertSave($staticSegmentManager, $staticSegment)
    {
        $staticSegmentManager->expects($this->once())
            ->method('persist')
            ->with($staticSegment);
        $staticSegmentManager->expects($this->once())
            ->method('flush');
    }
}
