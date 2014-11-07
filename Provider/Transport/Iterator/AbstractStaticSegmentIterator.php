<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

abstract class AbstractStaticSegmentIterator extends AbstractSubordinateIterator
{
    const MEMBER_ALIAS = 'mmb';

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
     * @var string
     */
    protected $memberClassName;

    /**
     * @param MarketingListProvider $marketingListProvider
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     * @param FieldHelper $fieldHelper
     * @param string $memberClassName
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        ContactInformationFieldsProvider $contactInformationFieldsProvider,
        FieldHelper $fieldHelper,
        $memberClassName
    ) {
        $this->marketingListProvider = $marketingListProvider;
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
        $this->fieldHelper = $fieldHelper;
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
     * @param MarketingList $marketingList
     *
     * @return QueryBuilder
     */
    protected function getIteratorQueryBuilder(MarketingList $marketingList)
    {
        if (!$this->memberClassName) {
            throw new \InvalidArgumentException('Member class name must be provided');
        }

        $qb = $this->marketingListProvider->getMarketingListEntitiesQueryBuilder($marketingList);

        $contactInformationFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $memberContactInformationFields = $this->contactInformationFieldsProvider->getEntityTypedFields(
            $this->memberClassName,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $expr = $qb->expr()->orX();
        foreach ($contactInformationFields as $contactInformationField) {
            foreach ($memberContactInformationFields as $memberContactInformationField) {
                $expr->add(
                    $qb->expr()->eq(
                        $this->fieldHelper->getFieldExpr($marketingList->getEntity(), $qb, $contactInformationField),
                        sprintf('%s.%s', self::MEMBER_ALIAS, $memberContactInformationField)
                    )
                );
            }
        }

        return $qb->leftJoin($this->memberClassName, self::MEMBER_ALIAS, Join::WITH, $expr);
    }
}
