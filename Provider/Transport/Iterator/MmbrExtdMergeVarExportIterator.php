<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Reader\SubordinateReaderInterface;

class MmbrExtdMergeVarExportIterator extends AbstractSubordinateIterator implements SubordinateReaderInterface
{
    /**
     * @var string
     */
    private $mmbrExtdMergeVarClassName;

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

        if (false === is_string($mmbrExtdMergeVarClassName) || empty($mmbrExtdMergeVarClassName)) {
            throw new \InvalidArgumentException('MemberExtendedMergeVar class must be a not empty string.');
        }

        $this->doctrineHelper = $doctrineHelper;
        $this->mmbrExtdMergeVarClassName = $mmbrExtdMergeVarClassName;
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
            ->getEntityManager($this->mmbrExtdMergeVarClassName)
            ->getRepository($this->mmbrExtdMergeVarClassName)
            ->createQueryBuilder('mmbrExtdMergeVar');

        $qb->select('mmbrExtdMergeVar')
            ->andWhere($qb->expr()->eq('mmbrExtdMergeVar.staticSegment', ':staticSegment'))
            ->andWhere($qb->expr()->notIn('mmbrExtdMergeVar.state', ':states'))
            ->setParameters(
                [
                    'staticSegment' => $staticSegment,
                    'states' => [ExtendedMergeVar::STATE_SYNCED, ExtendedMergeVar::STATE_DROPPED]
                ]
            )
            ->orderBy('mmbrExtdMergeVar.member');

        return new BufferedQueryResultIterator($qb);
    }
}
