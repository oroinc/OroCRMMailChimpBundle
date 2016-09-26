<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Writer\CleanUpInterface;
use Oro\Bundle\ImportExportBundle\Writer\InsertFromSelectWriter;

class MarketingListEmailWriter extends InsertFromSelectWriter implements CleanUpInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     * @return StaticSegmentMemberToRemoveWriter
     */
    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|EntityManager
     */
    protected function getEntityManager()
    {
        return $this->registry->getManager();
    }

    /**
     * {@inheritdoc}
     */
    public function cleanUp(array $item)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->entityName, 'e')
            ->where($qb->expr()->eq('IDENTITY(e.marketingList)', ':marketingList'))
            ->setParameter('marketingList', $item['marketing_list_id']);

        $qb->getQuery()->execute();
    }
}
