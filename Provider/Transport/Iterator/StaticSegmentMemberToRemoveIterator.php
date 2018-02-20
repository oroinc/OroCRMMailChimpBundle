<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;

class StaticSegmentMemberToRemoveIterator extends AbstractSubordinateIterator
{
    const QUERY_BUILDER = 'query_builder';
    const STATIC_SEGMENT_ID = 'static_segment_id';
    const STATE = 'state';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $memberToRemoveEntity;

    /**
     * @var string
     */
    protected $state;

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
     * @param string $entityClass
     * @return StaticSegmentMemberRemoveStateIterator
     */
    public function setMemberToRemoveEntity($entityClass)
    {
        $this->memberToRemoveEntity = $entityClass;

        return $this;
    }

    /**
     * @param string $state
     * @return StaticSegmentMemberToRemoveIterator
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @param StaticSegment $staticSegment
     * @return \Iterator
     */
    protected function createSubordinateIterator($staticSegment)
    {
        /** @var EntityManager $repository */
        $repository = $this->registry->getManager();
        $qb = $repository->createQueryBuilder();

        $qb->select(['IDENTITY(mmb.member) member_id'])
            ->from($this->memberToRemoveEntity, 'mmb')
            ->where($qb->expr()->eq('mmb.staticSegment', ':staticSegment'))
            ->andWhere($qb->expr()->eq('mmb.state', ':state'))
            ->setParameter('state', $this->state)
            ->setParameter('staticSegment', $staticSegment);

        return new \ArrayIterator(
            [
                [
                    self::QUERY_BUILDER => $qb,
                    self::STATIC_SEGMENT_ID => $staticSegment->getId(),
                    self::STATE => $this->state
                ]
            ]
        );
    }
}
