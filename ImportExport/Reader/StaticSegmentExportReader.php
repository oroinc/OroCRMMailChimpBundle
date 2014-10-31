<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\StaticSegmentExportListIterator;

class StaticSegmentExportReader extends AbstractIteratorBasedReader
{
    /**
     * @var string
     */
    protected $staticSegmentClassName;

    /**
     * @var string
     */
    protected $staticSegmentMemberClassName;

    /**
     * @param string $staticSegmentClassName
     */
    public function setStaticSegmentClassName($staticSegmentClassName)
    {
        $this->staticSegmentClassName = $staticSegmentClassName;
    }

    /**
     * @param string $staticSegmentMemberClassName
     */
    public function setStaticSegmentMemberClassName($staticSegmentMemberClassName)
    {
        $this->staticSegmentMemberClassName = $staticSegmentMemberClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->getSourceIterator()) {
            $iterator = new StaticSegmentExportListIterator($this->getSegmentsIterator());
            $iterator->setStaticSegmentMemberClassName($this->staticSegmentMemberClassName);
            $iterator->setDoctrineHelper($this->doctrineHelper);

            $this->setSourceIterator($iterator);
        }
    }

    /**
     * @return BufferedQueryResultIterator
     */
    protected function getSegmentsIterator()
    {
        $qb = $this->doctrineHelper
            ->getEntityManager($this->staticSegmentClassName)
            ->getRepository($this->staticSegmentClassName)
            ->createQueryBuilder('staticSegment');

        $qb
            ->select('staticSegment')
            ->where($qb->expr()->eq('staticSegment.syncStatus', ':syncStatus'))
            ->setParameter('syncStatus', StaticSegment::STATUS_NOT_SYNCED);

        return new BufferedQueryResultIterator($qb);
    }
}
