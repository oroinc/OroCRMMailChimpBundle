<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\ImportExportBundle\Writer\AbstractNativeQueryWriter;
use OroCRM\Bundle\MailChimpBundle\Entity\MarketingListEmail;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentMemberAddStateIterator extends AbstractSubordinateIterator
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $memberEntity;

    /**
     * @var string
     */
    protected $marketingListEmailEntity;

    /**
     * @param \Iterator|null $mainIterator
     */
    public function __construct(\Iterator $mainIterator = null)
    {
        if ($mainIterator) {
            $this->setMainIterator($mainIterator);
        }
    }

    /**
     * @param \Iterator $mainIterator
     */
    public function setMainIterator(\Iterator $mainIterator)
    {
        $this->mainIterator = $mainIterator;
    }

    /**
     * @param ManagerRegistry $registry
     * @return StaticSegmentMemberAddStateIterator
     */
    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * @param string $memberEntity
     * @return StaticSegmentMemberAddStateIterator
     */
    public function setMemberEntity($memberEntity)
    {
        $this->memberEntity = $memberEntity;

        return $this;
    }

    /**
     * @param string $marketingListEmailEntity
     * @return StaticSegmentMemberAddStateIterator
     */
    public function setMarketingListEmailEntity($marketingListEmailEntity)
    {
        $this->marketingListEmailEntity = $marketingListEmailEntity;

        return $this;
    }

    /**
     * @param StaticSegment $staticSegment
     *
     * @return \Iterator
     */
    protected function createSubordinateIterator($staticSegment)
    {
        /** @var EntityManager $repository */
        $repository = $this->registry->getManager();
        $qb = $repository->createQueryBuilder();

        $qb
            ->select(
                [
                    'mmb.id member_id',
                    $staticSegment->getId() . ' static_segment_id',
                    $qb->expr()->literal(StaticSegmentMember::STATE_ADD) . ' state'
                ]
            )
            ->from($this->marketingListEmailEntity, 'mlEmail')
            ->innerJoin(
                $this->memberEntity,
                'mmb',
                Join::WITH,
                $qb->expr()->eq('mmb.email', 'mlEmail.email')
            )
            ->leftJoin(
                'mmb.segmentMembers',
                'segmentMembers',
                Join::WITH,
                $qb->expr()->eq('segmentMembers.staticSegment', $staticSegment->getId())
            )
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('mlEmail.state', ':state'),
                    $qb->expr()->isNull('segmentMembers.id'),
                    $qb->expr()->isNotNull('mmb.originId'),
                    $qb->expr()->eq('mmb.subscribersList', ':subscribersList'),
                    $qb->expr()->eq('mlEmail.marketingList', ':marketingList')
                )
            )
            ->setParameter('state', MarketingListEmail::STATE_IN_LIST)
            ->setParameter('marketingList', $staticSegment->getMarketingList())
            ->setParameter('subscribersList', $staticSegment->getSubscribersList());

        return new \ArrayIterator(
            [
                [
                    AbstractNativeQueryWriter::QUERY_BUILDER => $qb,
                    'static_segment_id' => $staticSegment->getId()
                ]
            ]
        );
    }
}
