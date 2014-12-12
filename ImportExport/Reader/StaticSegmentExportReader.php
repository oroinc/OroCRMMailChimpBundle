<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
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
        if (!$this->staticSegmentMemberClassName) {
            throw new InvalidConfigurationException('StaticSegmentMember class name must be provided');
        }

        if (!$this->getSourceIterator()) {
            /** @var Channel $channel */
            $channel = $this->doctrineHelper->getEntityReference(
                $this->channelClassName,
                $context->getOption('channel')
            );

            $iterator = new StaticSegmentExportListIterator(
                $this->getSegmentsIterator($channel),
                $this->doctrineHelper
            );
            $iterator->setStaticSegmentMemberClassName($this->staticSegmentMemberClassName);

            $this->setSourceIterator($iterator);
        }
    }

    /**
     * @param Channel $channel
     *
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
            ->createQueryBuilder('staticSegment')
            ->select('staticSegment');

        $qb
            ->andWhere($qb->expr()->eq('staticSegment.channel', ':channel'))
            ->setParameter('channel', $channel);

        return new BufferedQueryResultIterator($qb);
    }
}
