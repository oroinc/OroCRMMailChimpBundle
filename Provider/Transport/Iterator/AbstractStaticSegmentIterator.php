<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

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
    protected $memberClassName;

    /**
     * @param MarketingListProvider $marketingListProvider
     * @param OwnershipMetadataProvider $ownershipMetadataProvider
     * @param string $memberClassName
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        OwnershipMetadataProvider $ownershipMetadataProvider,
        $memberClassName
    ) {
        $this->marketingListProvider = $marketingListProvider;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
        $this->memberClassName = $memberClassName;
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
        if (!$this->memberClassName) {
            throw new \InvalidArgumentException('Member class name must be provided');
        }

        $marketingList = $staticSegment->getMarketingList();
        $qb = $this->marketingListProvider->getMarketingListEntitiesQueryBuilder($marketingList);
        $qb->resetDQLPart('select');
        $this->applyOrganizationRestrictions($staticSegment, $qb);

        return $qb;
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
