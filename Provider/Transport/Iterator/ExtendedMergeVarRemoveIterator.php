<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\DecisionHandler;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionList;

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
     * @inheritdoc
     */
    protected function createSubordinateIterator($staticSegment)
    {
        if (false === $this->decisionHandler->isAllow($staticSegment->getMarketingList())) {
            return new \ArrayIterator(array());
        }

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
