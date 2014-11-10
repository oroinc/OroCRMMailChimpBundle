<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
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
        $this->assertStaticSegmentClassName();

        if (!$this->getSourceIterator()) {
            $iterator = new StaticSegmentExportListIterator($this->getSegmentsIterator(), $this->doctrineHelper);
            $iterator->setStaticSegmentMemberClassName($this->staticSegmentMemberClassName);

            $this->setSourceIterator($iterator);
        }
    }

    /**
     * @return BufferedQueryResultIterator
     */
    protected function getSegmentsIterator()
    {
        $this->assertStaticSegmentClassName();

        $qb = $this->doctrineHelper
            ->getEntityManager($this->staticSegmentClassName)
            ->getRepository($this->staticSegmentClassName)
            ->createQueryBuilder('staticSegment')
            ->select('staticSegment');

        return new BufferedQueryResultIterator($qb);
    }

    protected function assertStaticSegmentClassName()
    {
        if (!$this->staticSegmentClassName) {
            throw new InvalidConfigurationException('StaticSegment class name must be provided');
        }
    }
}
