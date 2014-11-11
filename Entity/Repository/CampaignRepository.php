<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;

class CampaignRepository extends EntityRepository
{
    /**
     * @return \Iterator
     */
    public function getSentCampaigns()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('c')
            ->from('OroCRMMailChimpBundle:Campaign', 'c')
            ->where($qb->expr()->eq('c.status', ':status'))
            ->setParameter('status', Campaign::STATUS_SENT);

        return new BufferedQueryResultIterator($qb);
    }
}
