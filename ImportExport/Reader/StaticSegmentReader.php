<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberSyncIterator;

class StaticSegmentReader extends IteratorBasedReader
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $marketingListClassName;

    /**
     * @var string
     */
    protected $staticSegmentClassName;

    /**
     * @param ContextRegistry $contextRegistry
     * @param DoctrineHelper $doctrineHelper
     * @param string $marketingListClassName
     * @param string $staticSegmentClassName
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        DoctrineHelper $doctrineHelper,
        $marketingListClassName,
        $staticSegmentClassName
    ) {
        $this->contextRegistry = $contextRegistry;
        $this->doctrineHelper = $doctrineHelper;
        $this->marketingListClassName = $marketingListClassName;
        $this->staticSegmentClassName = $staticSegmentClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($iterator = $this->getSourceIterator()) {
            $sourceIterator = clone $iterator;
            /** @var MemberSyncIterator $sourceIterator */
            $sourceIterator->setMainIterator($this->getStaticSegmentIterator());
            $this->setSourceIterator($sourceIterator);
        }
    }

    /**
     * @return BufferedQueryResultIterator
     */
    protected function getStaticSegmentIterator()
    {
        $qb = $this->doctrineHelper
            ->getEntityManager($this->staticSegmentClassName)
            ->getRepository($this->staticSegmentClassName)
            ->createQueryBuilder('staticSegment');

        $qb
            ->join($this->marketingListClassName, 'ml', Join::WITH, 'staticSegment.marketingList = ml.id')
            ->join('staticSegment.subscribersList', 'subscribersList');

        return new BufferedQueryResultIterator($qb);
    }
}
