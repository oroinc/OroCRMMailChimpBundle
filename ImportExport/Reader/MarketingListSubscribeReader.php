<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

class MarketingListSubscribeReader extends AbstractMarketingListReader
{
    /**
     * {@inheritdoc}
     */
    protected function getQueryIterator()
    {
        $qb = $this->getIteratorQueryBuilder($this->marketingList);

        $qb->andWhere($qb->expr()->isNull(self::MEMBER_ALIAS));

        $iterator = new BufferedQueryResultIterator($qb);
        $iterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);

        return $iterator;
    }
}
