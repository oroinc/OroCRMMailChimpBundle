<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;

class LoadMemberExportData extends LoadMemberData
{
    /**
     * @var array Channels configuration
     */
    protected $data = [
        [
            'originId' => 210000002,
            'email' => 'john.case@example.com',
            'status' => Member::STATUS_SUBSCRIBED,
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'channel' => 'mailchimp:channel_1',
            'reference' => 'mailchimp:member',
        ],
    ];
}
