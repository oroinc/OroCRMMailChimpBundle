<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

class LoadSegmentB2bCustomerData extends LoadSegmentData
{
    /**
     * @var array Channels configuration
     */
    protected $data = [
        [
            'type' => 'dynamic',
            'name' => 'Test ML B2b customer Segment',
            'description' => 'description',
            'entity' => B2bCustomer::class,
            'definition' => [
                'columns' => [
                    [
                        'name' => 'primaryEmail',
                        'label' => 'Primary Email',
                        'sorting' => '',
                        'func' => null,
                    ],
                ],
                'filters' => [
                    [
                        [
                            'columnName' => 'primaryEmail',
                            'criterion' => [
                                'filter' => 'string',
                                'data' => [
                                    'value' => '',
                                    'type' => 'filter_not_empty_option'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'reference' => 'mailchimp:ml_b2b_customer:segment',
        ],
    ];
}
