<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter\MemberSyncDataConverter;

class MemberSyncIterator extends AbstractStaticSegmentMembersIterator
{
    /**
     * @var DQLNameFormatter
     */
    protected $formatter;

    /**
     * @param DQLNameFormatter $formatter
     * @return MemberSyncIterator
     */
    public function setFormatter(DQLNameFormatter $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @param StaticSegment $staticSegment
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $qb = $this->getIteratorQueryBuilder($staticSegment);

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->setReverse(true);
        $bufferedIterator->setBufferSize(self::BUFFER_SIZE);

        return new \CallbackFilterIterator(
            $bufferedIterator,
            function (&$current) use ($staticSegment) {
                if (is_array($current)) {
                    $current['subscribersList_id'] = $staticSegment->getSubscribersList()->getId();
                    $current['entityClass']        = $staticSegment->getMarketingList()->getEntity();
                }
                return true;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorQueryBuilder(StaticSegment $staticSegment)
    {
        $qb = parent::getIteratorQueryBuilder($staticSegment);

        $this->addNameFields($staticSegment->getMarketingList()->getEntity(), $qb);

        // Select only members that are not in list yet
        $qb->andWhere($qb->expr()->isNull(sprintf('%s.id', self::MEMBER_ALIAS)));

        return $qb;
    }

    /**
     * @param string $entityName
     * @param QueryBuilder $qb
     */
    protected function addNameFields($entityName, QueryBuilder $qb)
    {
        /** @var From[] $from */
        $from = $qb->getDQLPart('from');
        $entityAlias = $from[0]->getAlias();
        $parts = $this->formatter->extractNamePartsPaths($entityName, $entityAlias);

        if (isset($parts['first_name'])) {
            $qb->addSelect(sprintf('%s AS %s', $parts['first_name'], MemberSyncDataConverter::FIRST_NAME_KEY));
        }
        if (isset($parts['last_name'])) {
            $qb->addSelect(sprintf('%s AS %s', $parts['last_name'], MemberSyncDataConverter::LAST_NAME_KEY));
        }
    }
}
