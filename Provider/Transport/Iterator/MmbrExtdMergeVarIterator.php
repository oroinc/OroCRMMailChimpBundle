<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\MarketingListQueryBuilderAdapter;

class MmbrExtdMergeVarIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var array
     */
    protected $uniqueMembers = [];

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper($doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param FieldHelper $fieldHelper
     */
    public function setFieldHelper($fieldHelper)
    {
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        parent::rewind();
        $this->uniqueMembers = [];
    }

    /**
     * @param StaticSegment $staticSegment
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $this->assertRequiredDependencies();

        if (!$staticSegment->getExtendedMergeVars()) {
            return new \EmptyIterator();
        }

        $qb = $this->getIteratorQueryBuilder($staticSegment);

        $marketingList = $staticSegment->getMarketingList();
        $fieldExpr = $this->fieldHelper
            ->getFieldExpr(
                $marketingList->getEntity(),
                $qb,
                $this->doctrineHelper->getSingleEntityIdentifierFieldName($marketingList->getEntity())
            );
        $qb->addSelect($fieldExpr . ' AS entity_id');
        $qb->addSelect(MarketingListQueryBuilderAdapter::MEMBER_ALIAS . '.id AS member_id');
        $qb->andWhere($qb->expr()->isNotNull(MarketingListQueryBuilderAdapter::MEMBER_ALIAS . '.id'));

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->setReverse(true);

        $uniqueMembers = &$this->uniqueMembers;

        return new \CallbackFilterIterator(
            $bufferedIterator,
            function (&$current) use ($staticSegment, &$uniqueMembers) {
                if (is_array($current)) {
                    if (!empty($current['member_id']) && in_array($current['member_id'], $uniqueMembers, true)) {
                        return false;
                    }
                    $current['subscribersList_id'] = $staticSegment->getSubscribersList()->getId();
                    $current['static_segment_id']  = $staticSegment->getId();
                    $uniqueMembers[] = $current['member_id'];
                    unset($current['id']);
                }
                return true;
            }
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function assertRequiredDependencies()
    {
        if (!$this->doctrineHelper) {
            throw new \InvalidArgumentException('DoctrineHelper must be provided.');
        }

        if (!$this->fieldHelper) {
            throw new \InvalidArgumentException('FieldHelper must be provided.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorQueryBuilder(StaticSegment $staticSegment)
    {
        $marketingList = $staticSegment->getMarketingList();

        if ($marketingList->isManual()) {
            $mixin = MarketingListProvider::MANUAL_RESULT_ENTITIES_MIXIN;
        } else {
            $mixin = MarketingListProvider::RESULT_ENTITIES_MIXIN;
        }

        $qb = clone $this->marketingListProvider->getMarketingListQueryBuilder($marketingList, $mixin);

        $this->marketingListQueryBuilderAdapter->prepareMarketingListEntities($staticSegment, $qb);

        return $qb;
    }
}
