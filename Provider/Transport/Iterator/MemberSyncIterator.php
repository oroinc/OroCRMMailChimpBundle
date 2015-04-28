<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\MarketingListQueryBuilderAdapter;

class MemberSyncIterator extends AbstractStaticSegmentIterator
{
    /**
     * @param StaticSegment $staticSegment
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $qb = $this->getIteratorQueryBuilder($staticSegment);

        $qb->andWhere($qb->expr()->isNull(MarketingListQueryBuilderAdapter::MEMBER_ALIAS));

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->setReverse(true);
        $bufferedIterator->setBufferSize(self::BUFFER_SIZE);

        return new \CallbackFilterIterator(
            $bufferedIterator,
            function (&$current) use ($staticSegment) {
                if (is_array($current)) {
                    $current['subscribersList_id'] = $staticSegment->getSubscribersList()->getId();
                    $current['entityClass']        = $staticSegment->getMarketingList()->getEntity();
                }
                return true;
            }
        );
    }
}
