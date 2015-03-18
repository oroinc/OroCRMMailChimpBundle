<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\DecisionHandler;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListFactory;

class ExtendedMergeVarAddIterator extends AbstractSubordinateIterator
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
     * @param string $mmbrExtdMergeVarClassName
     * @param ColumnDefinitionListFactory $columnDefinitionListFactory
     */
    public function __construct(
        DecisionHandler $decisionHandler,
        DoctrineHelper $doctrineHelper,
        $mmbrExtdMergeVarClassName,
        ColumnDefinitionListFactory $columnDefinitionListFactory
    ) {
        if (false === is_string($mmbrExtdMergeVarClassName) || empty($mmbrExtdMergeVarClassName)) {
            throw new \InvalidArgumentException('ExtendedMergeVar class name must be a not empty string.');
        }

        $this->decisionHandler = $decisionHandler;
        $this->doctrineHelper = $doctrineHelper;
        $this->extendedMergeVarClassName = $mmbrExtdMergeVarClassName;
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
     * {@inheritdoc}
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

        $qb->select('extendedMergeVar.name');
        $qb->andWhere($qb->expr()->eq('extendedMergeVar.staticSegment', ':staticSegment'));
        $qb->andWhere($qb->expr()->in('extendedMergeVar.name', ':vars'));
        $qb->andWhere($qb->expr()->notIn('extendedMergeVar.state', ':states'));
        $qb->setParameters(
            array(
                'staticSegment' => $staticSegment,
                'vars' => $vars,
                'states' => array(ExtendedMergeVar::STATE_REMOVE, ExtendedMergeVar::STATE_DROPPED)
            )
        );

        $existingVars = array_map(
            function ($each) {
                if (isset($each['name'])) {
                    return $each['name'];
                }
            },
            $qb->getQuery()->getArrayResult()
        );

        return new \CallbackFilterIterator(
            new \ArrayIterator($columnDefinitionList->getColumns()),
            function (&$current) use ($staticSegment, $existingVars) {
                if (is_array($current) && isset($current['name'])) {
                    if (in_array($current['name'], $existingVars)) {
                        return false;
                    }
                    $current['static_segment_id'] = $staticSegment->getId();
                    $current['state'] = ExtendedMergeVar::STATE_ADD;
                }
                return true;
            }
        );
    }
}
