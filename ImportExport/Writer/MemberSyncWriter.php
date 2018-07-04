<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Writer\CleanUpInterface;
use Oro\Bundle\ImportExportBundle\Writer\InsertFromSelectWriter;
use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MemberSyncWriter extends InsertFromSelectWriter implements CleanUpInterface
{
    /** @var bool */
    protected $hasFirstName = false;

    /** @var bool */
    protected $hasLastName = false;

    /** @var bool */
    protected $hasEmail = false;

    /** @var bool */
    protected $hasPhone = false;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var ContactInformationFieldsProvider */
    protected $contactInformationFieldsProvider;

    /**
     * @param ManagerRegistry $registry
     *
     * @return MemberSyncWriter
     */
    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * @param ContactInformationFieldsProvider $fieldsProvider
     */
    public function setFieldsProvider(ContactInformationFieldsProvider $fieldsProvider)
    {
        $this->contactInformationFieldsProvider = $fieldsProvider;
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
        $contactInformationFields = [];
        if ($this->hasEmail) {
            $contactInformationFields[] = 'email';
        }
        if ($this->hasPhone) {
            $contactInformationFields[] = 'phone';
        }
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

        $this->hasEmail = !empty($item['has_' . ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL]);
        $this->hasPhone = !empty($item['has_' . ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_PHONE]);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->entityName, 'e')
            ->where($qb->expr()->eq('IDENTITY(e.subscribersList)', ':subscribersList'))
            ->andWhere($qb->expr()->eq('e.status', ':status'))
            ->setParameter('status', Member::STATUS_EXPORT)
            ->setParameter('subscribersList', $item['subscribers_list_id']);

        $qb->getQuery()->execute();
    }
}
