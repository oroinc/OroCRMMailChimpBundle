<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter\MemberSyncDataConverter;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\MarketingListQueryBuilderAdapter;

abstract class AbstractStaticSegmentIterator extends AbstractSubordinateIterator
{
    const BUFFER_SIZE = 1000;

    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var DQLNameFormatter
     */
    protected $formatter;

    /**
     * @var MarketingListQueryBuilderAdapter
     */
    protected $marketingListQueryBuilderAdapter;

    /**
     * @var string
     */
    protected $removedItemClassName;

    /**
     * @var string
     */
    protected $unsubscribedItemClassName;

    /**
     * @var string
     */
    protected $segmentMemberClassName;

    /**
     * @param MarketingListProvider $marketingListProvider
     * @param DQLNameFormatter $formatter
     * @param MarketingListQueryBuilderAdapter $marketingListQueryBuilderAdapter
     * @param string $removedItemClassName
     * @param string $unsubscribedItemClassName
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        DQLNameFormatter $formatter,
        MarketingListQueryBuilderAdapter $marketingListQueryBuilderAdapter,
        $removedItemClassName,
        $unsubscribedItemClassName
    ) {
        $this->marketingListProvider            = $marketingListProvider;
        $this->formatter                        = $formatter;
        $this->marketingListQueryBuilderAdapter = $marketingListQueryBuilderAdapter;
        $this->removedItemClassName             = $removedItemClassName;
        $this->unsubscribedItemClassName        = $unsubscribedItemClassName;
    }

    /**
     * @param string $segmentMemberClassName
     */
    public function setSegmentMemberClassName($segmentMemberClassName)
    {
        $this->segmentMemberClassName = $segmentMemberClassName;
    }

    /**
     * @param \Iterator $mainIterator
     */
    public function setMainIterator(\Iterator $mainIterator)
    {
        $this->mainIterator = $mainIterator;
    }

    /**
     * @param StaticSegment $staticSegment
     *
     * @throws \InvalidArgumentException
     * @return QueryBuilder
     */
    protected function getIteratorQueryBuilder(StaticSegment $staticSegment)
    {
        $marketingList = $staticSegment->getMarketingList();
        $qb = $this->marketingListProvider->getMarketingListEntitiesQueryBuilder(
            $marketingList,
            MarketingListProvider::FULL_ENTITIES_MIXIN
        );

        $this->prepareIteratorPart($qb);

        /** @var From[] $from */
        $from = $qb->getDQLPart('from');
        $entityAlias = $from[0]->getAlias();
        $parts = $this->formatter->extractNamePartsPaths($marketingList->getEntity(), $entityAlias);

        $qb->resetDQLPart('select');
        if (isset($parts['first_name'])) {
            $qb->addSelect(sprintf('%s AS %s', $parts['first_name'], MemberSyncDataConverter::FIRST_NAME_KEY));
        }
        if (isset($parts['last_name'])) {
            $qb->addSelect(sprintf('%s AS %s', $parts['last_name'], MemberSyncDataConverter::LAST_NAME_KEY));
        }

        $this->marketingListQueryBuilderAdapter->prepareMarketingListEntities($staticSegment, $qb);

        return $qb;
    }

    /**
     * Method to change $qb for certain Iterator purposes
     *
     * @param QueryBuilder $qb
     */
    protected function prepareIteratorPart(QueryBuilder $qb)
    {
        if (!$this->removedItemClassName || !$this->unsubscribedItemClassName) {
            throw new \InvalidArgumentException('Removed and Unsubscribed Items Class names must be provided');
        }

        $rootAliases = $qb->getRootAliases();
        $entityAlias = reset($rootAliases);

        $qb
            ->leftJoin(
                $this->removedItemClassName,
                'mlr',
                Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('mlr.entityId', $entityAlias . '.id'),
                    $qb->expr()->eq('mlr.marketingList', 'mli.marketingList')
                )
            )
            ->andWhere($qb->expr()->isNull('mlr.id'))
            ->leftJoin(
                $this->unsubscribedItemClassName,
                'mlu',
                Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq('mlu.entityId', $entityAlias . '.id'),
                    $qb->expr()->eq('mlu.marketingList', 'mli.marketingList')
                )
            )
            ->andWhere($qb->expr()->isNull('mlu.id'));
    }
}
