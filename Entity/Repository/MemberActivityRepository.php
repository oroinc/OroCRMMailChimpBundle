<?php

namespace Oro\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class MemberActivityRepository extends EntityRepository
{
    /**
     * Return latest activityTime grouped by campaign.
     *
     * @param Channel $channel
     * @param array $actions
     * @return array
     */
    public function getLastSyncedActivitiesByCampaign(Channel $channel, array $actions = null)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $qb->select('c.originId as campaign_origin_id, a.action, MAX(a.activityTime) as activity_time')
            ->from($this->_entityName, 'a')
            ->join('a.campaign', 'c')
            ->where($qb->expr()->eq('a.channel', ':channel'))
            ->setParameter('channel', $channel)
            ->groupBy('c.originId, a.action');

        if ($actions) {
            $qb->andWhere(
                $qb->expr()->in('a.action', ':actions')
            )
            ->setParameter('actions', $actions);
        }

        $result = $qb->getQuery()->getArrayResult();
        $map = [];
        foreach ($result as $row) {
            $map[$row['campaign_origin_id']][$row['action']] = new \DateTime(
                $row['activity_time'],
                new \DateTimeZone('UTC')
            );
        }

        return $map;
    }
}
