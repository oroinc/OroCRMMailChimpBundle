<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\ImportExportBundle\Writer\AbstractNativeQueryWriter;
use Oro\Bundle\MailChimpBundle\Entity\MarketingListEmail;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use Oro\Bundle\MailChimpBundle\Model\FieldHelper;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MarketingListEmailIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @param ContactInformationFieldsProvider $provider
     * @return MarketingListEmailIterator
     */
    public function setContactInformationFieldsProvider(ContactInformationFieldsProvider $provider)
    {
        $this->contactInformationFieldsProvider = $provider;

        return $this;
    }

    /**
     * @param FieldHelper $fieldHelper
     * @return MarketingListEmailIterator
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
        $marketingList = $staticSegment->getMarketingList();
        $qb = $this->getIteratorQueryBuilder($staticSegment);

        $contactInformationFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );
        $emailField = reset($contactInformationFields);
        $contactInformationFieldExpr = $this->fieldHelper
            ->getFieldExpr($marketingList->getEntity(), $qb, $emailField);

        $qb
            ->select([
                $marketingList->getId() . ' as marketingListId',
                $contactInformationFieldExpr . ' as email',
                'CASE WHEN'
                    . ' MAX(mlr.id) IS NOT NULL THEN '
                        . $qb->expr()->literal(StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE)
                    . ' ELSE '
                    . ' CASE WHEN'
                        . ' MAX(mlu.id) IS NOT NULL THEN '
                            . $qb->expr()->literal(StaticSegmentMember::STATE_UNSUBSCRIBE)
                        . ' ELSE ' . $qb->expr()->literal(MarketingListEmail::STATE_IN_LIST)
                    . ' END'
                . ' END as state'
            ])
            ->andWhere($qb->expr()->isNotNull($contactInformationFieldExpr))
            ->resetDQLPart('orderBy')
            ->groupBy($contactInformationFieldExpr);

        return new \ArrayIterator(
            [
                [
                    AbstractNativeQueryWriter::QUERY_BUILDER => $qb,
                    'marketing_list_id' => $marketingList->getId()
                ]
            ]
        );
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
                    $qb->expr()->eq('mlr.marketingList', ':marketingListEntity')
                )
            )
            ->leftJoin(
                $this->unsubscribedItemClassName,
                'mlu',
                Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('mlu.entityId', $entityAlias . '.id'),
                    $qb->expr()->eq('mlu.marketingList', ':marketingListEntity')
                )
            );
    }
}
