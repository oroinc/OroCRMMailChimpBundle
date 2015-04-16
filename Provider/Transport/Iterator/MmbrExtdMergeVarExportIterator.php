<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar;
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

        if (!is_string($mmbrExtdMergeVarClassName) || empty($mmbrExtdMergeVarClassName)) {
            throw new \InvalidArgumentException('MemberExtendedMergeVar class name must be provided.');
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

        $qb
            ->select('mmbrExtdMergeVar')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('mmbrExtdMergeVar.staticSegment', ':staticSegment'),
                    $qb->expr()->notIn('mmbrExtdMergeVar.state', ':states')
                )
            )
            ->setParameter('staticSegment', $staticSegment)
            ->setParameter('states',[MemberExtendedMergeVar::STATE_SYNCED, MemberExtendedMergeVar::STATE_DROPPED]);

        return new BufferedQueryResultIterator($qb);
    }
}
