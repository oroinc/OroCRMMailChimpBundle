<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\QueryDecorator;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class MmbrExtdMergeVarIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var QueryDecorator
     */
    protected $queryDecorator;

    /**
     * @param QueryDecorator $queryDecorator
     */
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
        $this->queryDecorator->decorate($qb);

        $marketingList = $staticSegment->getMarketingList();
        $fieldExpr = $this->fieldHelper
            ->getFieldExpr(
                $marketingList->getEntity(),
                $qb,
                'id'
            );
        $qb->addSelect($fieldExpr . ' AS entity_id');
        $qb->addSelect(self::MEMBER_ALIAS . '.id AS member_id');
        $qb->addSelect($qb->expr()->literal(MemberExtendedMergeVar::STATE_ADD) . ' state');

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->setReverse(true);

        return new \CallbackFilterIterator(
            $bufferedIterator,
            function (&$current) use ($staticSegment) {
                if (is_array($current)) {
                    $current['subscribersList_id'] = $staticSegment->getSubscribersList()->getId();
                    $current['static_segment_id']  = $staticSegment->getId();
                }
                return true;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorQueryBuilder(StaticSegment $staticSegment)
    {
        if (!$this->memberClassName) {
            throw new \InvalidArgumentException('Member class name must be provided');
        }

        $marketingList = $staticSegment->getMarketingList();

        if ($marketingList->isManual()) {
            $mixin = MarketingListProvider::MANUAL_RESULT_ENTITIES_MIXIN;
        } else {
            $mixin = MarketingListProvider::RESULT_ENTITIES_MIXIN;
        }

        $qb = clone $this->marketingListProvider->getMarketingListQueryBuilder($marketingList, $mixin);

        $contactInformationFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $expr = $qb->expr()->orX();
        foreach ($contactInformationFields as $contactInformationField) {
            $contactInformationFieldExpr = $this->fieldHelper
                ->getFieldExpr($marketingList->getEntity(), $qb, $contactInformationField);

            $expr->add(
                $qb->expr()->eq(
                    $contactInformationFieldExpr,
                    sprintf('%s.%s', self::MEMBER_ALIAS, self::MEMBER_EMAIL_FIELD)
                )
            );
        }

        $organization = $staticSegment->getChannel()->getOrganization();
        $metadata = $this->ownershipMetadataProvider->getMetadata($marketingList->getEntity());

        if ($organization && $fieldName = $metadata->getOrganizationFieldName()) {
            $aliases = $qb->getRootAliases();
            $qb->andWhere(
                $qb->expr()->eq(
                    sprintf('%s.%s', reset($aliases), $fieldName),
                    ':organization'
                )
            );

            $qb->setParameter('organization', $organization);
        }

        $qb
            ->leftJoin(
                $this->memberClassName,
                self::MEMBER_ALIAS,
                Join::WITH,
                $qb->expr()->andX(
                    $expr,
                    $qb->expr()->eq(sprintf('%s.subscribersList', self::MEMBER_ALIAS), ':subscribersList')
                )
            )
            ->setParameter('subscribersList', $staticSegment->getSubscribersList()->getId());

        return $qb;
    }
}
