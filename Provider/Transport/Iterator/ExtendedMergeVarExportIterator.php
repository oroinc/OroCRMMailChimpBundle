<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Reader\SubordinateReaderInterface;

class ExtendedMergeVarExportIterator extends AbstractSubordinateIterator implements SubordinateReaderInterface
{
    /**
     * @var string
     */
    private $extendedMergeVarClassName;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @param \Iterator $mainIterator
     * @param DoctrineHelper $doctrineHelper
     * @param string $mmbrExtdMergeVarClassName
     */
    public function __construct(
        \Iterator $mainIterator,
        DoctrineHelper $doctrineHelper,
        $mmbrExtdMergeVarClassName
    ) {
        parent::__construct($mainIterator);

        if (!is_string($mmbrExtdMergeVarClassName) || empty($mmbrExtdMergeVarClassName)) {
            throw new \InvalidArgumentException('ExtendedMergeVar class must be provided.');
        }

        $this->doctrineHelper = $doctrineHelper;
        $this->extendedMergeVarClassName = $mmbrExtdMergeVarClassName;
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
        $qb = $this->doctrineHelper
            ->getEntityManager($this->extendedMergeVarClassName)
            ->getRepository($this->extendedMergeVarClassName)
            ->createQueryBuilder('extendedMergeVar');

        $qb->select('extendedMergeVar')
            ->andWhere($qb->expr()->eq('extendedMergeVar.staticSegment', ':staticSegment'))
            ->andWhere($qb->expr()->notIn('extendedMergeVar.state', ':states'))
            ->setParameters(
                [
                    'staticSegment' => $staticSegment,
                    'states' => [ExtendedMergeVar::STATE_SYNCED, ExtendedMergeVar::STATE_DROPPED]
                ]
            )
            ->orderBy('extendedMergeVar.staticSegment');

        return new BufferedQueryResultIterator($qb);
    }
}
