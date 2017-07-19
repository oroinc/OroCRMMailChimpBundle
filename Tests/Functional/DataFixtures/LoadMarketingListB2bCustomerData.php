<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEmailData;

class LoadMarketingListB2bCustomerData extends LoadMarketingListData
{
    /**
     * @var array Channels configuration
     */
    protected $mlData = [
        [
            'type' => 'dynamic',
            'name' => 'Test B2bCustomer ML',
            'description' => '',
            'entity' => B2bCustomer::class,
            'reference' => 'mailchimp:ml_b2b_customer',
            'segment' => 'mailchimp:ml_b2b_customer:segment',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadB2bCustomerEmailData::class,
            LoadSegmentB2bCustomerData::class
        ];
    }
}
