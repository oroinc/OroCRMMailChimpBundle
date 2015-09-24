<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\StaticSegmentMemberToRemoveIterator;

class StaticSegmentMemberRemoveStateWriter implements ItemWriterInterface
{
    const QUERY_BUILDER = 'query_builder';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param string $entityName
     * @return StaticSegmentMemberRemoveStateWriter
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            /** @var QueryBuilder $itemsQb */
            $itemsQb = $item[StaticSegmentMemberToRemoveIterator::QUERY_BUILDER];
            $staticSegmentId = $item[StaticSegmentMemberToRemoveIterator::STATIC_SEGMENT_ID];

            $updateQb = $this->getEntityManager()->createQueryBuilder();
            $updateQb
                ->update($this->entityName, 'e')
                ->set('e.state', ':state')
                ->where($updateQb->expr()->in('e.member', ':items'))
                ->andWhere($updateQb->expr()->eq('IDENTITY(e.staticSegment)', ':staticSegmentId'))
                ->setParameter('state', StaticSegmentMember::STATE_REMOVE)
                ->setParameter('staticSegmentId', $staticSegmentId)
                ->setParameter('items', $itemsQb->getQuery());

            $updateQb->getQuery()->execute();
        }
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->registry->getManagerForClass($this->entityName);
        }

        return $this->em;
    }
}
