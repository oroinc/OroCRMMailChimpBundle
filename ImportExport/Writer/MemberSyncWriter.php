<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;

class MemberSyncWriter extends AbstractInsertFromSelectWriter implements CleanUpInterface
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
     * {@inheritdoc}
     */
    protected function getQueryBuilder($item)
    {
        $this->hasFirstName = !empty($item['has_first_name']);
        $this->hasLastName = !empty($item['has_last_name']);

        return parent::getQueryBuilder($item);
    }

    /**
     * {@inheritdoc}
     */
    protected function getInsert()
    {
        $fields = ['email'];
        if ($this->hasFirstName) {
            $fields[] = 'first_name';
        }
        if ($this->hasLastName) {
            $fields[] = 'last_name';
        }
        $fields = array_merge(
            $fields,
            [
                'owner_id',
                'subscribers_list_id',
                'channel_id',
                'status',
                'created_at',
                'merge_var_values',
            ]
        );

        return 'INSERT INTO orocrm_mailchimp_member(' . implode(',', $fields) . ')';
    }

    /**
     * {@inheritdoc}
     */
    public function cleanUp(array $item)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->entityName, 'e')
            ->where($qb->expr()->eq('IDENTITY(e.subscribersList)', ':subscribersList'))
            ->andWhere($qb->expr()->eq('e.status', ':status'))
            ->setParameter('status', Member::STATUS_EXPORT)
            ->setParameter('subscribersList', $item['subscribers_list_id']);

        $qb->getQuery()->execute();
    }
}
