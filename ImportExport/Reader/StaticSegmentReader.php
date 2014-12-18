<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Exception\InvalidConfigurationException;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberSyncIterator;

class StaticSegmentReader extends AbstractIteratorBasedReader
{
    /**
     * @var string
     */
    protected $marketingListClassName;

    /**
     * @var string
     */
    protected $staticSegmentClassName;

    /**
     * @param string $marketingListClassName
     */
    public function setMarketingListClassName($marketingListClassName)
    {
        $this->marketingListClassName = $marketingListClassName;
    }

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

        if (!$this->marketingListClassName) {
            throw new InvalidConfigurationException('MarketingList class name must be provided');
        }

        if (!$this->staticSegmentClassName) {
            throw new InvalidConfigurationException('StaticSegment class name must be provided');
        }

        if ($iterator = $this->getSourceIterator()) {
            $sourceIterator = clone $iterator;

            /** @var Channel $channel */
            $channel = $this->doctrineHelper->getEntityReference(
                $this->channelClassName,
                $context->getOption('channel')
            );
            /** @var MemberSyncIterator $sourceIterator */
            $sourceIterator->setMainIterator($this->getStaticSegmentIterator($channel));
            $this->setSourceIterator($sourceIterator);
        }
    }

    /**
     * @param Channel $channel
     *
     * @return BufferedQueryResultIterator
     */
    protected function getStaticSegmentIterator(Channel $channel)
    {
        $qb = $this->doctrineHelper
            ->getEntityManager($this->staticSegmentClassName)
            ->getRepository($this->staticSegmentClassName)
            ->createQueryBuilder('staticSegment');

        $qb
            ->join($this->marketingListClassName, 'ml', Join::WITH, 'staticSegment.marketingList = ml.id')
            ->join('staticSegment.subscribersList', 'subscribersList')
            ->andWhere($qb->expr()->eq('staticSegment.channel', ':channel'))
            ->setParameter('channel', $channel);

        return new BufferedQueryResultIterator($qb);
    }
}
