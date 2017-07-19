<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;

class LoadStaticSegmentB2bCustomerData extends LoadStaticSegmentData
{
    /**
     * @var array Segment configuration
     */
    protected $segmentData = [
        [
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'marketingList' => 'mailchimp:ml_b2b_customer',
            'channel' => 'mailchimp:channel_1',
            'name' => 'Test',
            'sync_status' => StaticSegment::STATUS_SCHEDULED,
            'remote_remove' => '0',
            'reference' => 'mailchimp:segment_b2b',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadMarketingListB2bCustomerData::class, LoadSubscribersListData::class];
    }
}
