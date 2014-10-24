<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Segment;

use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\Segment;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class Sync
{
    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @param MarketingListProvider $marketingListProvider
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        ContactInformationFieldsProvider $contactInformationFieldsProvider
    ) {
        $this->marketingListProvider = $marketingListProvider;
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
    }

    /**
     * @param Segment $segment
     */
    public function sync(Segment $segment)
    {
        $marketingList = $segment->getMarketingList();

        $subscribeIterator = $this->getSubscribeIterator($marketingList);
        foreach ($subscribeIterator as $memberToSubscribe) {
        }
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return \Iterator
     */
    protected function getSubscribeIterator(MarketingList $marketingList)
    {
        $qb = $this->marketingListProvider->getMarketingListEntitiesQueryBuilder($marketingList);

        $emails = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        /** @var From $from */
        $from = reset($qb->getDQLPart('from'));
        $conditions = array_map(
            function ($field) use ($from) {
                return sprintf('member.email = %s.%s', $from->getAlias(), $field);
            },
            $emails
        );

        $qb->leftJoin('OroCRMMailChimpBundle:Member', 'member', Join::ON, implode(' OR ', $conditions));

        return new BufferedQueryResultIterator($qb);
    }
}
