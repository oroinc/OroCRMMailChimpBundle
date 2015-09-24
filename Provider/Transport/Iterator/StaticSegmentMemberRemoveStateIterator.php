<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Writer\AbstractNativeQueryWriter;

class StaticSegmentMemberRemoveStateIterator extends AbstractSubordinateIterator
{
    /**
     * @var ManagerRegistry
     */
    protected  $registry;

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
     * @return StaticSegmentMemberRemoveStateIterator
     */
    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * @param string $memberEntity
     * @return StaticSegmentMemberRemoveStateIterator
     */
    public function setMemberEntity($memberEntity)
    {
        $this->memberEntity = $memberEntity;

        return $this;
    }

    /**
     * @param string $marketingListEmailEntity
     * @return StaticSegmentMemberRemoveStateIterator
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
                    'IDENTITY(segmentMembers.staticSegment) static_segment_id'
                ]
            )
            ->from($this->memberEntity, 'mmb')
            ->innerJoin(
                'mmb.segmentMembers',
                'segmentMembers',
                Join::WITH,
                $qb->expr()->eq('segmentMembers.staticSegment', $staticSegment->getId())
            )
            ->leftJoin(
                $this->marketingListEmailEntity,
                'mlEmail',
                Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('mmb.email', 'mlEmail.email'),
                    $qb->expr()->eq('mlEmail.marketingList', ':marketingList')
                )
            )
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->isNull('mlEmail'),
                    $qb->expr()->isNotNull('mmb.originId'),
                    $qb->expr()->eq('mmb.subscribersList', ':subscribersList')
                )
            )
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
