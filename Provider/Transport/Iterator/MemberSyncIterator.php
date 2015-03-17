<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\QueryDecorator;

class MemberSyncIterator extends AbstractStaticSegmentIterator
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
        $qb = $this->getIteratorQueryBuilder($staticSegment);
        $marketingList = $staticSegment->getMarketingList();
        $fieldExpr = $this->fieldHelper
            ->getFieldExpr(
                $marketingList->getEntity(), $qb, 'id'
            );
        $qb->addSelect($fieldExpr . ' AS entity_id');

        if ($staticSegment->getExtendedMergeVars()) {
            $this->queryDecorator->decorate($qb, $staticSegment);
        }

        $qb->andWhere($qb->expr()->isNull(self::MEMBER_ALIAS));

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->setReverse(true);

        return new \CallbackFilterIterator(
            $bufferedIterator,
            function (&$current) use ($staticSegment) {
                if (is_array($current)) {
                    $current['subscribersList_id'] = $staticSegment->getSubscribersList()->getId();
                    $current['entityClass']        = $staticSegment->getMarketingList()->getEntity();
                    if ($staticSegment->getExtendedMergeVars()) {
                        $current['extended_merge_vars'] = $staticSegment->getExtendedMergeVars();
                    }
                }
                return true;
            }
        );
    }
}
