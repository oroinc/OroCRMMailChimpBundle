<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

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

        return new BufferedQueryResultIterator($qb);
    }
}
