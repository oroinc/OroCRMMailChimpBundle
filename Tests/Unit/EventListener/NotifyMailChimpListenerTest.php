<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\EventListener\NotifyMailChimpListener;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Event\UpdateMarketingListEvent;
use Oro\Component\Testing\Unit\EntityTrait;

class NotifyMailChimpListenerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var NotifyMailChimpListener
     */
    private $listener;

    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $doctrineHelper;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    protected function setUp()
    {
        $this->repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->listener = new NotifyMailChimpListener($this->doctrineHelper);
    }

    public function testUpdateStaticSegmentSyncStatus()
    {
        /** @var MarketingList $marketingList */
        $marketingList = $this->getEntity(MarketingList::class, ['id' => 1]);

        /** @var StaticSegment $staticSegment */
        $staticSegment = $this->getEntity(StaticSegment::class, ['id' => 1]);

        $event = new UpdateMarketingListEvent();
        $event->addMarketingList($marketingList);

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['marketingList' => $marketingList])
            ->willReturn([$staticSegment]);

        $this->listener->onMarketingListChange($event);

        $this->assertSame(StaticSegment::STATUS_SCHEDULED_BY_CHANGE, $staticSegment->getSyncStatus());
    }
}
