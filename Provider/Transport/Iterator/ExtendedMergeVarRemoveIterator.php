<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\DecisionHandler;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionList;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListFactory;

class ExtendedMergeVarRemoveIterator extends AbstractSubordinateIterator
{
    /**
     * @var DecisionHandler
     */
    private $decisionHandler;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var string
     */
    private $extendedMergeVarClassName;

    /**
     * @var ColumnDefinitionListFactory
     */
    private $columnDefinitionListFactory;

    /**
     * @param DecisionHandler $decisionHandler
     * @param DoctrineHelper $doctrineHelper
     * @param string $extendedMergeVarClassName
     * @param ColumnDefinitionListFactory $columnDefinitionListFactory
     */
    public function __construct(
        DecisionHandler $decisionHandler,
        DoctrineHelper $doctrineHelper,
        $extendedMergeVarClassName,
        ColumnDefinitionListFactory $columnDefinitionListFactory
    ) {
        if (false === is_string($extendedMergeVarClassName) || empty($extendedMergeVarClassName)) {
            throw new \InvalidArgumentException('ExtendedMergeVar class name must be a not empty string.');
        }

        $this->decisionHandler = $decisionHandler;
        $this->doctrineHelper = $doctrineHelper;
        $this->extendedMergeVarClassName = $extendedMergeVarClassName;
        $this->columnDefinitionListFactory = $columnDefinitionListFactory;
    }

    /**
     * @param \Iterator $mainIterator
     */
    public function setMainIterator(\Iterator $mainIterator)
    {
        $this->mainIterator = $mainIterator;
    }

    /**
     * @inheritdoc
     */
    protected function createSubordinateIterator($staticSegment)
    {
        if (false === $this->decisionHandler->isAllow($staticSegment->getMarketingList())) {
            return new \ArrayIterator(array());
        }

        $columnDefinitionList = $this->columnDefinitionListFactory
            ->create($staticSegment->getMarketingList());

        $vars = array_map(
            function ($each) {
                return $each['name'];
            },
            $columnDefinitionList->getColumns()
        );

        $qb = $this->doctrineHelper
            ->getEntityManager($this->extendedMergeVarClassName)
            ->getRepository($this->extendedMergeVarClassName)
            ->createQueryBuilder('extendedMergeVar');

        $qb->select(
            array(
                'extendedMergeVar.id',
                $staticSegment->getId() . ' static_segment_id',
                'extendedMergeVar.name',
                $qb->expr()->literal(ExtendedMergeVar::STATE_REMOVE) . ' state'
            )
        );

        $qb->andWhere($qb->expr()->eq('extendedMergeVar.staticSegment', ':staticSegment'));
        $qb->andWhere($qb->expr()->notIn('extendedMergeVar.name', ':vars'));

        $qb->setParameters(
            array(
                'staticSegment' => $staticSegment,
                'vars' => $vars
            )
        );

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->setReverse(true);

        return $bufferedIterator;
    }
}
