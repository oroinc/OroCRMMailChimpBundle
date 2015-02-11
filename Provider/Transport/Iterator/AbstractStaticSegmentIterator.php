<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;
use OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter\MemberSyncDataConverter;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

abstract class AbstractStaticSegmentIterator extends AbstractSubordinateIterator
{
    const MEMBER_ALIAS = 'mmb';
    const MEMBER_EMAIL_FIELD = 'email';

    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $ownershipMetadataProvider;

    /**
     * @var DQLNameFormatter
     */
    protected $formatter;

    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @param MarketingListProvider $marketingListProvider
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     * @param FieldHelper $fieldHelper
     * @param OwnershipMetadataProvider $ownershipMetadataProvider
     * @param string $memberClassName
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        ContactInformationFieldsProvider $contactInformationFieldsProvider,
        FieldHelper $fieldHelper,
        OwnershipMetadataProvider $ownershipMetadataProvider,
        DQLNameFormatter $formatter,
        $memberClassName
    ) {
        $this->marketingListProvider            = $marketingListProvider;
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
        $this->fieldHelper                      = $fieldHelper;
        $this->ownershipMetadataProvider        = $ownershipMetadataProvider;
        $this->formatter                        = $formatter;
        $this->memberClassName                  = $memberClassName;
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
     * @return QueryBuilder
     */
    protected function getIteratorQueryBuilder(StaticSegment $staticSegment)
    {
        if (!$this->memberClassName) {
            throw new \InvalidArgumentException('Member class name must be provided');
        }

        $marketingList = $staticSegment->getMarketingList();
        $qb = $this->marketingListProvider->getMarketingListEntitiesQueryBuilder($marketingList);

        /** @var From[] $from */
        $from = $qb->getDQLPart('from');
        $entityAlias = $from[0]->getAlias();
        $parts = $this->formatter->extractNamePartsPaths($marketingList->getEntity(), $entityAlias);

        $qb->resetDQLPart('select');
        if (isset($parts['first_name'])) {
            $qb->addSelect(sprintf('%s AS %s', $parts['first_name'], MemberSyncDataConverter::FIRST_NAME_KEY));
        }
        if (isset($parts['last_name'])) {
            $qb->addSelect(sprintf('%s AS %s', $parts['last_name'], MemberSyncDataConverter::LAST_NAME_KEY));
        }

        $contactInformationFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $expr = $qb->expr()->orX();
        foreach ($contactInformationFields as $contactInformationField) {
            $contactInformationFieldExpr = $this->fieldHelper
                ->getFieldExpr($marketingList->getEntity(), $qb, $contactInformationField);

            $qb->addSelect($contactInformationFieldExpr. ' AS ' .$contactInformationField);
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
