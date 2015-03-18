<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\QueryDecorator;

class MmbrExtdMergeVarIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var QueryDecorator
     */
    private $queryDecorator;

    public function setExtendedMergeVarQueryDecorator(QueryDecorator $queryDecorator)
    {
        $this->queryDecorator = $queryDecorator;
    }

    /**
     * @param StaticSegment $staticSegment
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        if (!$staticSegment->getExtendedMergeVars()) {
            return new \ArrayIterator(array());
        }

        $qb = $this->getIteratorQueryBuilder($staticSegment);
        $marketingList = $staticSegment->getMarketingList();
        $fieldExpr = $this->fieldHelper
            ->getFieldExpr(
                $marketingList->getEntity(), $qb, 'id'
            );
        $qb->addSelect($fieldExpr . ' AS entity_id');
        $qb->addSelect(self::MEMBER_ALIAS . '.id AS member_id');

        $this->queryDecorator->decorate($qb, $staticSegment);

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->setReverse(true);

        return new \CallbackFilterIterator(
            $bufferedIterator,
            function (&$current) use ($staticSegment) {
                if (is_array($current)) {
                    $current['subscribersList_id'] = $staticSegment->getSubscribersList()->getId();
                    $current['entityClass']        = $staticSegment->getMarketingList()->getEntity();
                    $current['static_segment_id']  = $staticSegment->getId();
                    if ($staticSegment->getExtendedMergeVars()) {
                        $current['extended_merge_vars'] = $staticSegment->getExtendedMergeVars();
                    }
                }
                return true;
            }
        );
    }
}
