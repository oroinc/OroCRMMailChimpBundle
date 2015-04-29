<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class StaticSegmentExportReader extends AbstractIteratorBasedReader
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
        if (!$this->getSourceIterator()) {
            /** @var Channel $channel */
            $channel = $this->doctrineHelper->getEntityReference(
                $this->channelClassName,
                $context->getOption('channel')
            );

            $this->setSourceIterator($this->getSegmentsIterator($channel, $context->getOption('segments')));
        }
    }

    /**
     * @param Channel    $channel
     * @param array|null $segments
     *
     * @return BufferedQueryResultIterator
     * @throws InvalidConfigurationException
     */
    protected function getSegmentsIterator(Channel $channel, array $segments = null)
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

        if ($segments) {
            $qb
                ->andWhere($qb->expr()->in('staticSegment.id', ':segments'))
                ->setParameter('segments', $segments);
        }

        return new BufferedQueryResultIterator($qb);
    }
}
