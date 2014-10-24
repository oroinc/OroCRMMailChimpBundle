<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Transport;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class SyncTest extends WebTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    protected function setUp()
    {
        $this->initClient();
        $this->clientFactory = $this->getMockBuilder(
            'OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClientFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(
                ['create']
            )
            ->getMock();
        $this->client = $this->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient')
            ->disableOriginalConstructor()
            ->setMethods(
                ['export', 'getLists', 'getListMergeVars']
            )
            ->getMock();
        $transport = new MailChimpTransport($this->clientFactory, $this->getContainer()->get('doctrine'));
        $this->getContainer()->set('orocrm_mailchimp.transport.integration_transport', $transport);
        $this->loadFixtures(['OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadChannelData']);
    }

    /**
     * @dataProvider commandOptionsProvider
     */
    public function testCommand($commandName, array $params, $entity, $data, $assertMethod, $assertCount, $expectedList)
    {
        $this->client->expects($this->once())
            ->method('getLists')
            ->will($this->returnValue($data));
        $this->clientFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->client));

        if (isset($params['--integration-id'])) {
            $params['--integration-id'] = (string)$this->getReference(
                'mailchimp_transport:test_transport' . $params['--integration-id']
            )->getId();
        }
        $result = $this->runCommand($commandName, $params);
        foreach ($expectedList as $expected) {
            $this->assertContains($expected, $result);
        }
        if ($assertMethod) {
            $listRepo = $this->getContainer()->get('doctrine')->getRepository('OroCRMMailChimpBundle:' . $entity);
            $list = $listRepo->findAll();
            $this->$assertMethod($assertCount, count($list));
        }
    }

    /**
     * @return array
     */
    public function commandOptionsProvider()
    {
        return [
            'SubscribersListSyncCommand' => [
                'commandName'     => 'oro:cron:integration:sync',
                'params'          => ['--integration-id' => '1', '--connector' => 'list'],
                'entity'          => 'SubscribersList',
                'data'            => [
                    'total' => 2,
                    'data' => [
                        [
                            'id' => 'f04749dd92',
                            'web_id' => 460025,
                            'name' => 'List #####5',
                            'date_created' => '2014-10-17 19:06:38',
                            'email_type_option' => false,
                            'use_awesomebar' => true,
                            'default_from_name' => 'Ignat Shcheglovskyi',
                            'default_from_email' => 'ischeglovskiy@magecore.com',
                            'default_subject' => '',
                            'default_language' => 'en',
                            'list_rating' => 0,
                            'subscribe_url_short' => 'http://eepurl.com/55JSn',
                            'subscribe_url_long' => 'http://orocrm.us9.list-manage.com/subscribe?u=30a1fbd85f',
                            'beamer_address' => 'us9-b538643d4f-cb1fc7e5b4@inbound.mailchimp.com',
                            'visibility' => 'pub',
                            'stats' =>
                                [
                                    'member_count' => 0,
                                    'unsubscribe_count' => 0,
                                    'cleaned_count' => 0,
                                    'member_count_since_send' => 0,
                                    'unsubscribe_count_since_send' => 0,
                                    'cleaned_count_since_send' => 0,
                                    'campaign_count' => 2,
                                    'grouping_count' => 0,
                                    'group_count' => 0,
                                    'merge_var_count' => 2,
                                    'avg_sub_rate' => 0,
                                    'avg_unsub_rate' => 0,
                                    'target_sub_rate' => 0,
                                    'open_rate' => 0,
                                    'click_rate' => 0,
                                    'date_last_campaign' => null,
                                ],
                            'modules' => [],
                        ],
                        [
                            'id' => 'f04749dd93',
                            'web_id' => 460026,
                            'name' => 'List #####6',
                            'date_created' => '2014-10-17 19:06:38',
                            'email_type_option' => false,
                            'use_awesomebar' => true,
                            'default_from_name' => 'Ignat Shcheglovskyi',
                            'default_from_email' => 'ischeglovskiy@magecore.com',
                            'default_subject' => '',
                            'default_language' => 'en',
                            'list_rating' => 0,
                            'subscribe_url_short' => 'http://eepurl.com/55JSn',
                            'subscribe_url_long' => 'http://orocrm.us9.list-manage.com/subscribe?u=30a1fbd85fafe93f6446fef6e&id=f04749dd92',
                            'beamer_address' => 'us9-b538643d4f-cb1fc7e5b4@inbound.mailchimp.com',
                            'visibility' => 'pub',
                            'stats' =>
                                [
                                    'member_count' => 0,
                                    'unsubscribe_count' => 0,
                                    'cleaned_count' => 0,
                                    'member_count_since_send' => 0,
                                    'unsubscribe_count_since_send' => 0,
                                    'cleaned_count_since_send' => 0,
                                    'campaign_count' => 2,
                                    'grouping_count' => 0,
                                    'group_count' => 0,
                                    'merge_var_count' => 2,
                                    'avg_sub_rate' => 0,
                                    'avg_unsub_rate' => 0,
                                    'target_sub_rate' => 0,
                                    'open_rate' => 0,
                                    'click_rate' => 0,
                                    'date_last_campaign' => null,
                                ],
                            'modules' => [],
                        ]
                    ],
                    'errors' => []
                ],
                'assertMethod'    => 'assertEquals',
                'assertCount'     => '2',
                'expectedContent' => [
                    'Run sync for "mailchimp1" integration.',
                    'Start processing "list" connector',
                    'invalid entities: [0]',
                    'process [2]',
                    'delete [0]',
                    'updated [0]',
                    'read [2]',
                    'added [2]',
                ]
            ],
        ];
    }
}
