<?php

namespace Oro\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MailChimpBundle\Entity\Campaign;

class CampaignRepository extends EntityRepository
{
    /**
     * Return all sent campaigns, that are allowed for receiving activities and are fresh enough.
     *
     * @param Channel $channel
     * @return \Iterator
     */
    public function getSentCampaigns(Channel $channel)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('c')
            ->from('OroMailChimpBundle:Campaign', 'c')
            ->innerJoin('c.emailCampaign', 'emailCampaign')
            ->innerJoin(
                'OroMailChimpBundle:MailChimpTransportSettings',
                'transportSettings',
                Join::WITH,
                $qb->expr()->eq('IDENTITY(emailCampaign.transportSettings)', 'transportSettings.id')
            )
            ->where($qb->expr()->eq('c.status', ':status'))
            ->andWhere($qb->expr()->eq('c.channel', ':channel'))
            ->andWhere($qb->expr()->eq('transportSettings.receiveActivities', ':receiveActivities'))
            ->setParameter('status', Campaign::STATUS_SENT)
            ->setParameter('channel', $channel)
            ->setParameter('receiveActivities', true);

        $updateInterval = $channel->getTransport()->getSettingsBag()->get('activityUpdateInterval');
        if ($updateInterval) {
            $qb->andWhere($qb->expr()->gte('DATE_ADD(c.sendTime, :updateInterval, \'day\')', ':now'))
                ->setParameter('updateInterval', (int)$updateInterval)
                ->setParameter('now', new \DateTime('now', new \DateTimeZone('UTC')));
        }

        return new BufferedIdentityQueryResultIterator($qb);
    }
}
