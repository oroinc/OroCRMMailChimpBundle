<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Reader\SubordinateReaderInterface;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\DecisionHandler;

class ExtendedMergeVarExportIterator extends AbstractSubordinateIterator implements SubordinateReaderInterface
{
    /**
     * @var string
     */
    private $extendedMergeVarClassName;

    /**
     * @var DecisionHandler
     */
    private $decisionHandler;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @param \Iterator $mainIterator
     * @param DecisionHandler $decisionHandler
     * @param DoctrineHelper $doctrineHelper
     * @param string $extendedMergeVarClassName
     */
    public function __construct(
        \Iterator $mainIterator,
        DecisionHandler $decisionHandler,
        DoctrineHelper $doctrineHelper,
        $extendedMergeVarClassName
    ) {
        parent::__construct($mainIterator);

        if (false === is_string($extendedMergeVarClassName) || empty($extendedMergeVarClassName)) {
            throw new \InvalidArgumentException('ExtendedMergeVar class must be a not empty string.');
        }

        $this->decisionHandler = $decisionHandler;
        $this->doctrineHelper = $doctrineHelper;
        $this->extendedMergeVarClassName = $extendedMergeVarClassName;
    }

    /**
     * @return bool
     */
    public function writeRequired()
    {
        if (!$this->subordinateIterator) {
            return false;
        }

        return !$this->subordinateIterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        if (false === $this->decisionHandler->isAllow($staticSegment->getMarketingList())) {
            return new \ArrayIterator(array());
        }

        $qb = $this->doctrineHelper
            ->getEntityManager($this->extendedMergeVarClassName)
            ->getRepository($this->extendedMergeVarClassName)
            ->createQueryBuilder('extendedMergeVar');

        $qb->select('extendedMergeVar')
            ->andWhere($qb->expr()->eq('extendedMergeVar.staticSegment', ':staticSegment'))
            ->andWhere($qb->expr()->notIn('extendedMergeVar.state', ':states'))
            ->setParameters(
                array(
                    'staticSegment' => $staticSegment,
                    'states' => array(ExtendedMergeVar::STATE_SYNCED)
                )
            )
            ->orderBy('extendedMergeVar.staticSegment');

        return new BufferedQueryResultIterator($qb);
    }
}
