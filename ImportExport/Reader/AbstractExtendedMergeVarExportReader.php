<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

abstract class AbstractExtendedMergeVarExportReader extends AbstractIteratorBasedReader
{
    /**
     * @var string
     */
    protected $staticSegmentClassName;

    /**
     * @param string $staticSegmentClassName
     */
    public function setStaticSegmentClassName($staticSegmentClassName)
    {
        $this->staticSegmentClassName = $staticSegmentClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);

        if (!$this->getSourceIterator()) {
            /** @var Channel $channel */
            $channel = $this->doctrineHelper->getEntityReference(
                $this->channelClassName,
                $context->getOption('channel')
            );

            $iterator = $this->getExtendedMergeVarIterator($channel);

            $this->setSourceIterator($iterator);
        }
    }

    /**
     * @param Channel $channel
     * @return \Iterator
     */
    abstract protected function getExtendedMergeVarIterator(Channel $channel);

    /**
     * @param Channel $channel
     * @return BufferedQueryResultIterator
     */
    protected function getSegmentsIterator(Channel $channel)
    {
        if (!$this->staticSegmentClassName) {
            throw new InvalidConfigurationException('StaticSegment class name must be provided');
        }

        $qb = $this->doctrineHelper
            ->getEntityManager($this->staticSegmentClassName)
            ->getRepository($this->staticSegmentClassName)
            ->createQueryBuilder('staticSegment');

        $qb
            ->select('staticSegment')
            ->andWhere($qb->expr()->eq('staticSegment.channel', ':channel'))
            ->setParameter('channel', $channel);

        return new BufferedQueryResultIterator($qb);
    }
}
