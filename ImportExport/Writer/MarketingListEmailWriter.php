<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

class MarketingListEmailWriter extends AbstractInsertFromSelectWriter implements CleanUpInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getInsert()
    {
        return 'INSERT INTO orocrm_mailchimp_ml_email(marketing_list_id, email, state)';
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
