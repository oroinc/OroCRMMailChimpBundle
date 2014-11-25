<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\EventListener;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Entity\ProcessTrigger;
use Oro\Bundle\WorkflowBundle\Event\ProcessHandleEvent;
use Oro\Bundle\WorkflowBundle\EventListener\ProcessCollectorListener;
use Oro\Bundle\WorkflowBundle\Model\ProcessData;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;
use OroCRM\Bundle\MailChimpBundle\EventListener\ProcessListener;

class ProcessListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProcessCollectorListener
     */
    protected $processListener;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processListener = $this
            ->getMockBuilder('Oro\Bundle\WorkflowBundle\EventListener\ProcessCollectorListener')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new ProcessListener($this->doctrineHelper, $this->processListener);
    }

    /**
     * @param array $data
     * @param bool $expectsDisable
     * @param bool $expectedFlush
     * @param bool $exception
     *
     * @dataProvider eventDataProvider
     */
    public function testOnProcessHandleAfter(array $data, $expectsDisable, $expectedFlush, $exception = false)
    {
        $trigger = new ProcessTrigger();
        $data = new ProcessData($data);
        $event = new ProcessHandleEvent($trigger, $data);

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($em));

        if ($expectsDisable) {
            $this->processListener
                ->expects($this->at(0))
                ->method('setEnabled')
                ->with(false);

            $this->processListener
                ->expects($this->at(1))
                ->method('setEnabled')
                ->with(true);
        }

        if ($expectedFlush) {
            $em
                ->expects($this->once())
                ->method('flush')
                ->with($this->isType('object'));
        } else {
            if ($exception) {
                $em
                    ->expects($this->once())
                    ->method('flush')
                    ->will($this->throwException(new \Exception()));

                $this->setExpectedException('\Exception');
            } else {
                $em
                    ->expects($this->never())
                    ->method('flush');
            }
        }

        $this->listener->onProcessHandleAfter($event);
    }

    /**
     * @return array
     */
    public function eventDataProvider()
    {
        return [
            'empty' => [
                [],
                false,
                false
            ],
            'activity' => [
                ['data' => new MemberActivity()],
                true,
                true
            ],
            'flush failed' => [
                ['data' => new MemberActivity()],
                true,
                false,
                true,
            ]
        ];
    }
}
