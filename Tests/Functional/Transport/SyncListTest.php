<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Transport;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClientFactory;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class SyncListTest extends WebTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MailChimpClientFactory
     */
    protected $entityBody;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    /**
     * @var \Guzzle\Http\Message\Response
     */
    protected $response;

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
                ['export', 'getLists', 'getListMergeVars', 'getListStaticSegments']
            )
            ->getMock();
        $this->clientFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->client));
//
//        $this->entityBody = $this->getMockBuilder('Guzzle\Http\EntityBody\EntityBody')
//            ->disableOriginalConstructor()
//            ->setMethods(
//                ['seek', 'readLine']
//            )
//            ->getMock();
//        $this->response = $this->getMockBuilder('Guzzle\Http\Message\Response')
//            ->disableOriginalConstructor()
//            ->getMock();
//        $this->client->expects($this->any())
//            ->method('export')
//            ->will($this->returnValue($this->response));
//        $this->response->expects($this->any())
//            ->method('getBody')
//            ->will($this->returnValue($this->entityBody));

        $transport = new MailChimpTransport($this->clientFactory, $this->getContainer()->get('doctrine'));
        $this->getContainer()->set('orocrm_mailchimp.transport.integration_transport', $transport);
        $this->loadFixtures(['OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData']);
    }

    /**
     * @dataProvider commandListOptionsProvider
     * @param string $commandName
     * @param array $params
     * @param string $mockMethod
     * @param string $entity
     * @param array $data
     * @param string $assertMethod
     * @param int $assertCount
     * @param array $expectedList
     */
    public function testSyncList(
        $commandName,
        array $params,
        $mockMethod,
        $entity,
        $data,
        $assertMethod,
        $assertCount,
        $expectedList
    ) {
        $this->client->expects($this->once())
            ->method($mockMethod)
            ->will($this->returnValue($data));

        if (isset($params['--integration-id'])) {
            $params['--integration-id'] = (string)$this->getReference(
                'mailchimp:channel_' . $params['--integration-id']
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function commandListOptionsProvider()
    {
        return [
            'SubscribersListSyncCommand' => [
                'commandName' => 'oro:cron:integration:sync',
                'params' => ['--integration-id' => '1', '--connector' => 'list'],
                'mockMethod' => 'getLists',
                'entity' => 'SubscribersList',
                'data' => [
                    'total' => 2,
                    'data' => [
                        [
                            'id' => 'f04749dd92',
                            'web_id' => 460025,
                            'name' => 'Example List #1',
                            'date_created' => '2014-10-17 19:06:38',
                            'email_type_option' => false,
                            'use_awesomebar' => true,
                            'default_from_name' => 'John Doe',
                            'default_from_email' => 'john.doe@example.com',
                            'default_subject' => '',
                            'default_language' => 'en',
                            'list_rating' => 0,
                            'subscribe_url_short' => 'http://eepurl.com/30a1fbd',
                            'subscribe_url_long' => 'http://list-manage.com/subscribe?u=30a1fbd',
                            'beamer_address' => '30a1fbd@inbound.mailchimp.com',
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
                            'name' => 'Example List #2',
                            'date_created' => '2014-10-17 19:06:38',
                            'email_type_option' => false,
                            'use_awesomebar' => true,
                            'default_from_name' => 'John Doe',
                            'default_from_email' => 'john.doe@example.com',
                            'default_subject' => '',
                            'default_language' => 'en',
                            'list_rating' => 0,
                            'subscribe_url_short' => 'http://eepurl.com/30a1fbd',
                            'subscribe_url_long' => 'http://list-manage.com/subscribe?u=30a1fbd',
                            'beamer_address' => '30a1fbd@inbound.mailchimp.com',
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
                'assertMethod' => 'assertEquals',
                'assertCount' => '2',
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
