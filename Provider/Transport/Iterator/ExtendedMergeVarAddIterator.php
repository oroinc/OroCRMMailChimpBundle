<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\DecisionHandler;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionList;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

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
     * @param DecisionHandler $decisionHandler
     * @param DoctrineHelper $doctrineHelper
     * @param string $extendedMergeVarClassName
     */
    public function __construct(
        DecisionHandler $decisionHandler,
        DoctrineHelper $doctrineHelper,
        $extendedMergeVarClassName
    ) {
        if (false === is_string($extendedMergeVarClassName) || empty($extendedMergeVarClassName)) {
            throw new \InvalidArgumentException('ExtendedMergeVar class name must be a not empty string.');
        }

        $this->decisionHandler = $decisionHandler;
        $this->doctrineHelper = $doctrineHelper;
        $this->extendedMergeVarClassName = $extendedMergeVarClassName;
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

        /** @var Segment $segment */
        $segment = $staticSegment->getMarketingList()->getSegment();
        $columnDefinitionList = new ColumnDefinitionList($segment);

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
        $qb->setParameters(
            array(
                'staticSegment' => $staticSegment,
                'vars' => $vars
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
            $columnDefinitionList->getIterator(),
            function (&$current) use ($staticSegment, $existingVars) {
                if (is_array($current) && isset($current['name'])) {
                    if (in_array($current['name'], $existingVars)) {
                        return false;
                    }
                    $current['static_segment_id'] = $staticSegment->getId();
                }
                return true;
            }
        );
    }
}
