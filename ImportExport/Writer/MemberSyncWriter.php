<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\ImportExportBundle\Writer\CleanUpInterface;
use Oro\Bundle\ImportExportBundle\Writer\InsertFromSelectWriter;
use Oro\Bundle\MailChimpBundle\Entity\Member;

class MemberSyncWriter extends InsertFromSelectWriter implements CleanUpInterface
{
    /**
     * @var bool
     */
    protected $hasFirstName = false;

    /**
     * @var bool
     */
    protected $hasLastName = false;

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
    public function getFields()
    {
        $contactInformationFields = ['email'];
        if ($this->hasFirstName) {
            $contactInformationFields[] = 'firstName';
        }
        if ($this->hasLastName) {
            $contactInformationFields[] = 'lastName';
        }

        return array_merge($contactInformationFields, $this->fields);
    }

    /**
     * {@inheritdoc}
     */
    public function cleanUp(array $item)
    {
        $this->hasFirstName = !empty($item['has_first_name']);
        $this->hasLastName = !empty($item['has_last_name']);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->entityName, 'e')
            ->where($qb->expr()->eq('IDENTITY(e.subscribersList)', ':subscribersList'))
            ->andWhere($qb->expr()->eq('e.status', ':status'))
            ->setParameter('status', Member::STATUS_EXPORT)
            ->setParameter('subscribersList', $item['subscribers_list_id']);

        $qb->getQuery()->execute();
    }
}
