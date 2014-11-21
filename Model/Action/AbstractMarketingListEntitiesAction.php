<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Action;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

abstract class AbstractMarketingListEntitiesAction extends AbstractAction
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @param ContextAccessor $contextAccessor
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     * @param MarketingListProvider $marketingListProvider
     * @param FieldHelper $fieldHelper
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        ContactInformationFieldsProvider $contactInformationFieldsProvider,
        MarketingListProvider $marketingListProvider,
        FieldHelper $fieldHelper
    ) {
        parent::__construct($contextAccessor);

        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
        $this->marketingListProvider = $marketingListProvider;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @param MarketingList $marketingList
     * @param string $email
     * @return QueryBuilder
     */
    protected function getMarketingListEntitiesByEmailQueryBuilder(MarketingList $marketingList, $email)
    {
        $emailFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $qb = $this->getEntitiesQueryBuilder($marketingList);

        $expr = $qb->expr()->orX();
        foreach ($emailFields as $emailField) {
            $parameterName = $emailField . mt_rand();
            $expr->add(
                $qb->expr()->eq(
                    $this->fieldHelper->getFieldExpr($marketingList->getEntity(), $qb, $emailField),
                    ':' . $parameterName
                )
            );
            $qb->setParameter($parameterName, $email);
        }
        $qb->andWhere($expr);

        return $qb;
    }

    /**
     * @param MarketingList $marketingList
     * @param string $email
     * @return BufferedQueryResultIterator
     */
    protected function getMarketingListEntitiesByEmail(MarketingList $marketingList, $email)
    {
        return new BufferedQueryResultIterator(
            $this->getMarketingListEntitiesByEmailQueryBuilder($marketingList, $email)
        );
    }

    /**
     * @param MarketingList $marketingList
     * @return QueryBuilder
     */
    protected function getEntitiesQueryBuilder(MarketingList $marketingList)
    {
        return $this->marketingListProvider->getMarketingListEntitiesQueryBuilder($marketingList);
    }
}
