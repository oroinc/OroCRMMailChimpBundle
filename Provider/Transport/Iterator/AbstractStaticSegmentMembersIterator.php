<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use Oro\Bundle\MailChimpBundle\Model\FieldHelper;

abstract class AbstractStaticSegmentMembersIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @param string $memberClassName
     * @return MemberSyncIterator
     */
    public function setMemberClassName($memberClassName)
    {
        $this->memberClassName = $memberClassName;

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

    /**
     * {@inheritdoc}
     */
    protected function getIteratorQueryBuilder(StaticSegment $staticSegment)
    {
        $qb = parent::getIteratorQueryBuilder($staticSegment);
        $qb->resetDQLPart('select');
        $this->matchMembersByEmail($staticSegment, $qb);

        return $qb;
    }
}
