<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar;

use Doctrine\ORM\QueryBuilder;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;

class QueryDecorator
{
    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param FieldHelper $fieldHelper
     */
    public function __construct(FieldHelper $fieldHelper)
    {
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param StaticSegment $staticSegment
     */
    public function decorate(QueryBuilder $queryBuilder, StaticSegment $staticSegment)
    {
        if ($staticSegment->getExtendedMergeVars()) {
            $marketingList = $staticSegment->getMarketingList();
            foreach ($staticSegment->getExtendedMergeVars() as $var) {
                if (false !== strpos($var->getName(), 'item_')) {
                    continue;
                }
                $varFieldExpr = $this->fieldHelper
                    ->getFieldExpr(
                        $marketingList->getEntity(), $queryBuilder, $var->getName()
                    );
                $queryBuilder->addSelect($varFieldExpr . ' AS ' . $var->getNameWithPrefix());
            }
        }
    }
}
