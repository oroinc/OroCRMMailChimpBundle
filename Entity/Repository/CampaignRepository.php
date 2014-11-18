<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;

class CampaignRepository extends EntityRepository
{
    /**
     * @param Channel $channel
     * @return \Iterator
     */
    public function getSentCampaigns(Channel $channel)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('c')
            ->from('OroCRMMailChimpBundle:Campaign', 'c')
            ->where($qb->expr()->eq('c.status', ':status'))
            ->andWhere('c.channel = :channel')
            ->setParameter('status', Campaign::STATUS_SENT)
            ->setParameter('channel', $channel);

        return new BufferedQueryResultIterator($qb);
    }
}
