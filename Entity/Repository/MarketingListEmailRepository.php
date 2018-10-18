<?php

namespace Oro\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\MailChimpBundle\Entity\MarketingListEmail;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

class MarketingListEmailRepository extends EntityRepository
{
    /**
     * @param MarketingList $marketingList
     * @return array
     */
    public function findInListByMarketingList(MarketingList $marketingList)
    {
        $qb = $this->createQueryBuilder('marketing_list_email');
        $qb->where('marketing_list_email.state = :state');
        $qb->andWhere('marketing_list_email.marketingList = :marketingList');
        $qb->setParameter('state', MarketingListEmail::STATE_IN_LIST);
        $qb->setParameter('marketingList', $marketingList);
        $qb->select('marketing_list_email.email');

        $marketingListEmails = $qb->getQuery()->getArrayResult();
        return array_column($marketingListEmails, 'email');
    }
}
