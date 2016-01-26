<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;

abstract class AbstractStaticSegmentIterator extends AbstractSubordinateIterator
{
    const MEMBER_ALIAS = 'mmb';
    const MEMBER_EMAIL_FIELD = 'email';
    const BUFFER_SIZE = 1000;

    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $ownershipMetadataProvider;

    /**
     * @var string
     */
    protected $removedItemClassName;

    /**
     * @var string
     */
    protected $unsubscribedItemClassName;

    /**
     * @var string
     */
    protected $segmentMemberClassName;

    /**
     * @param MarketingListProvider $marketingListProvider
     * @param OwnershipMetadataProvider $ownershipMetadataProvider
     * @param string $removedItemClassName
     * @param string $unsubscribedItemClassName
     * @internal param MarketingListQueryBuilderAdapter $marketingListQueryBuilderAdapter
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        OwnershipMetadataProvider $ownershipMetadataProvider,
        $removedItemClassName,
        $unsubscribedItemClassName
    ) {
        $this->marketingListProvider            = $marketingListProvider;
        $this->ownershipMetadataProvider        = $ownershipMetadataProvider;
        $this->removedItemClassName             = $removedItemClassName;
        $this->unsubscribedItemClassName        = $unsubscribedItemClassName;
    }

    /**
     * @param string $segmentMemberClassName
     */
    public function setSegmentMemberClassName($segmentMemberClassName)
    {
        $this->segmentMemberClassName = $segmentMemberClassName;
    }

    /**
     * @param \Iterator $mainIterator
     */
    public function setMainIterator(\Iterator $mainIterator)
    {
        $this->mainIterator = $mainIterator;
    }

    /**
     * @param StaticSegment $staticSegment
     *
     * @throws \InvalidArgumentException
     * @return QueryBuilder
     */
    protected function getIteratorQueryBuilder(StaticSegment $staticSegment)
    {
        $marketingList = $staticSegment->getMarketingList();
        $qb = $this->marketingListProvider->getMarketingListEntitiesQueryBuilder(
            $marketingList,
            MarketingListProvider::FULL_ENTITIES_MIXIN
        );

        $this->prepareIteratorPart($qb);
        $this->applyOrganizationRestrictions($staticSegment, $qb);

        return $qb;
    }

    /**
     * Method to change $qb for certain Iterator purposes
     *
     * @param QueryBuilder $qb
     */
    protected function prepareIteratorPart(QueryBuilder $qb)
    {
        if (!$this->removedItemClassName || !$this->unsubscribedItemClassName) {
            throw new \InvalidArgumentException('Removed and Unsubscribed Items Class names must be provided');
        }

        $rootAliases = $qb->getRootAliases();
        $entityAlias = reset($rootAliases);

        $qb
            ->leftJoin(
                $this->removedItemClassName,
                'mlr',
                Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('mlr.entityId', $entityAlias . '.id'),
                    $qb->expr()->eq('mlr.marketingList', 'mli.marketingList')
                )
            )
            ->andWhere($qb->expr()->isNull('mlr.id'))
            ->leftJoin(
                $this->unsubscribedItemClassName,
                'mlu',
                Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('mlu.entityId', $entityAlias . '.id'),
                    $qb->expr()->eq('mlu.marketingList', 'mli.marketingList')
                )
            )
            ->andWhere($qb->expr()->isNull('mlu.id'));
    }

    /**
     * @param StaticSegment $staticSegment
     * @param QueryBuilder $qb
     */
    protected function applyOrganizationRestrictions(StaticSegment $staticSegment, QueryBuilder $qb)
    {
        $organization = $staticSegment->getChannel()->getOrganization();
        $metadata = $this->ownershipMetadataProvider
            ->getMetadata($staticSegment->getMarketingList()->getEntity());

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
    }
}
