<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter\MemberSyncDataConverter;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;

class MemberSyncIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var DQLNameFormatter
     */
    protected $formatter;

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @param DQLNameFormatter $formatter
     * @return MemberSyncIterator
     */
    public function setFormatter(DQLNameFormatter $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @param ContactInformationFieldsProvider $provider
     * @return MemberSyncIterator
     */
    public function setContactInformationFieldsProvider(ContactInformationFieldsProvider $provider)
    {
        $this->contactInformationFieldsProvider = $provider;

        return $this;
    }

    /**
     * @param FieldHelper $fieldHelper
     * @return MemberSyncIterator
     */
    public function setFieldHelper(FieldHelper $fieldHelper)
    {
        $this->fieldHelper = $fieldHelper;

        return $this;
    }

    /**
     * @param StaticSegment $staticSegment
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $qb = $this->getIteratorQueryBuilder($staticSegment);

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

    /**
     * {@inheritdoc}
     */
    protected function getIteratorQueryBuilder(StaticSegment $staticSegment)
    {
        $qb = parent::getIteratorQueryBuilder($staticSegment);

        $this->addNameFields($staticSegment->getMarketingList()->getEntity(), $qb);
        $this->matchMembersByEmail($staticSegment, $qb);

        // Select only members that are not in list yet
        $qb->andWhere($qb->expr()->isNull(self::MEMBER_ALIAS));

        return $qb;
    }

    /**
     * @param string $entityName
     * @param QueryBuilder $qb
     */
    protected function addNameFields($entityName, QueryBuilder $qb)
    {
        /** @var From[] $from */
        $from = $qb->getDQLPart('from');
        $entityAlias = $from[0]->getAlias();
        $parts = $this->formatter->extractNamePartsPaths($entityName, $entityAlias);

        if (isset($parts['first_name'])) {
            $qb->addSelect(sprintf('%s AS %s', $parts['first_name'], MemberSyncDataConverter::FIRST_NAME_KEY));
        }
        if (isset($parts['last_name'])) {
            $qb->addSelect(sprintf('%s AS %s', $parts['last_name'], MemberSyncDataConverter::LAST_NAME_KEY));
        }
    }

    /**
     * @param StaticSegment $staticSegment
     * @param QueryBuilder $qb
     */
    protected function matchMembersByEmail(StaticSegment $staticSegment, QueryBuilder $qb)
    {
        $marketingList = $staticSegment->getMarketingList();
        $contactInformationFields = $this->getContactInformationFields($marketingList);

        $expr = $qb->expr()->orX();
        foreach ($contactInformationFields as $contactInformationField) {
            $contactInformationFieldExpr = $this->fieldHelper
                ->getFieldExpr($marketingList->getEntity(), $qb, $contactInformationField);

            $qb->addSelect($contactInformationFieldExpr . ' AS ' . $contactInformationField);
            $expr->add(
                $qb->expr()->eq(
                    $contactInformationFieldExpr,
                    sprintf('%s.%s', self::MEMBER_ALIAS, self::MEMBER_EMAIL_FIELD)
                )
            );
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
    }

    /**
     * @param MarketingList $marketingList
     * @return array
     */
    protected function getContactInformationFields(MarketingList $marketingList)
    {
        $contactInformationFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        return $contactInformationFields;
    }
}
