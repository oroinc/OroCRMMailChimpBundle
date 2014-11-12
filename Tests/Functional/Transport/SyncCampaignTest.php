<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Transport;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClientFactory;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class SyncCampaignTest extends WebTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $apiClient;

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
        $this->apiClient = $this->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient')
            ->disableOriginalConstructor()
            ->setMethods(
                ['export', 'getLists', 'getListMergeVars', 'getCampaigns']
            )
            ->getMock();
        $this->clientFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->apiClient));

        $transport = new MailChimpTransport($this->clientFactory, $this->getContainer()->get('doctrine'));
        $this->getContainer()->set('orocrm_mailchimp.transport.integration_transport', $transport);
        $this->loadFixtures(['OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData']);
    }

    /**
     * @dataProvider commandCampaignOptionsProvider
     * @param string $commandName
     * @param array $params
     * @param string $entity
     * @param array $data
     * @param string $assertMethod
     * @param int $assertCount
     * @param array $expectedList
     */
    public function testSyncCampaign(
        $commandName,
        array $params,
        $entity,
        $data,
        $assertMethod,
        $assertCount,
        $expectedList
    ) {
        $this->apiClient->expects($this->once())
            ->method('getCampaigns')
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
    public function commandCampaignOptionsProvider()
    {
        return [
            'SubscribersMemberActivitySyncCommand' => [
                'commandName' => 'oro:cron:integration:sync',
                'params' => ['--integration-id' => '1', '--connector' => 'campaign'],
                'entity' => 'Campaign',
                'data' => [
                    'total' => 2,
                    'data' => [
                        array (
                            'id' => '4c34206f0b',
                            'web_id' => 638833,
                            'list_id' => '1a2925d57e',
                            'folder_id' => 0,
                            'template_id' => 91,
                            'content_type' => 'template',
                            'content_edited_by' => 'Oro CRM',
                            'title' => 'Test C ML',
                            'type' => 'regular',
                            'create_time' => '2014-11-10 17:38:55',
                            'send_time' => '2014-11-10 17:40:27',
                            'content_updated_time' => '2014-11-10 17:40:26',
                            'status' => 'sent',
                            'from_name' => 'Makar',
                            'from_email' => 'sichevoy@gmail.com',
                            'subject' => 'Test C',
                            'to_name' => '*|FNAME|*',
                            'archive_url' => 'http://eepurl.com/73jez',
                            'archive_url_long' => 'http://us9.campaign-archive1.com/?u=30a1fbd85fafe93f6446fef6e&id=4c34206f0b',
                            'emails_sent' => 2,
                            'inline_css' => false,
                            'analytics' => 'N',
                            'analytics_tag' => '',
                            'authenticate' => true,
                            'ecomm360' => false,
                            'auto_tweet' => false,
                            'auto_fb_post' => NULL,
                            'auto_footer' => false,
                            'timewarp' => false,
                            'timewarp_schedule' => NULL,
                            'tracking' =>
                                array (
                                    'html_clicks' => true,
                                    'text_clicks' => true,
                                    'opens' => true,
                                ),
                            'parent_id' => '',
                            'is_child' => false,
                            'tests_sent' => '0',
                            'tests_remain' => 12,
                            'segment_text' => '<ul id="conditions" class="conditions"><li class="nomargin"><span class="small-meta">Matching <strong>any</strong> conditions:</span></li><li>Static Segments member is part of <strong>Test ML<strong></li></ul><span>For a total of <strong>2</strong> emails sent.</span>',
                            'segment_opts' =>
                                array (
                                    'match' => 'any',
                                    'conditions' =>
                                        array (
                                            0 =>
                                                array (
                                                    'field' => 'static_segment',
                                                    'op' => 'eq',
                                                    'value' => 30261,
                                                ),
                                        ),
                                ),
                            'saved_segment' =>
                                array (
                                    'id' => 30261,
                                    'type' => 'static',
                                    'name' => 'Test ML',
                                ),
                            'type_opts' =>
                                array (
                                ),
                            'comments_total' => 0,
                            'comments_unread' => 0,
                            'summary' =>
                                array (
                                    'syntax_errors' => 0,
                                    'hard_bounces' => 0,
                                    'soft_bounces' => 0,
                                    'unsubscribes' => 0,
                                    'abuse_reports' => 0,
                                    'forwards' => 0,
                                    'forwards_opens' => 0,
                                    'opens' => 0,
                                    'last_open' => NULL,
                                    'unique_opens' => 0,
                                    'clicks' => 0,
                                    'unique_clicks' => 0,
                                    'users_who_clicked' => 0,
                                    'last_click' => NULL,
                                    'emails_sent' => 2,
                                    'unique_likes' => 0,
                                    'recipient_likes' => 0,
                                    'facebook_likes' => 0,
                                    'industry' =>
                                        array (
                                            'type' => 'Software and Web App',
                                            'open_rate' => 0.20547381614224999,
                                            'click_rate' => 0.024960592576843001,
                                            'bounce_rate' => 0.055762297273677999,
                                            'unopen_rate' => 0.73876388658407,
                                            'unsub_rate' => 0.0071395657266407004,
                                            'abuse_rate' => 0.00063851068202262003,
                                        ),
                                    'absplit' =>
                                        array (
                                        ),
                                    'timewarp' =>
                                        array (
                                        ),
                                    'timeseries' =>
                                        array (
                                            0 =>
                                                array (
                                                    'timestamp' => '2014-11-10 17:00:00',
                                                    'emails_sent' => 2,
                                                    'unique_opens' => 0,
                                                    'recipients_click' => 0,
                                                ),
                                        ),
                                ),
                            'social_card' => NULL,
                        ),
                        array (
                            'id' => '4134206f0b',
                            'web_id' => 618833,
                            'list_id' => '112925d57e',
                            'folder_id' => 0,
                            'template_id' => 91,
                            'content_type' => 'template',
                            'content_edited_by' => 'Oro CRM',
                            'title' => 'Test C ML',
                            'type' => 'regular',
                            'create_time' => '2014-11-10 17:38:55',
                            'send_time' => '2014-11-10 17:40:27',
                            'content_updated_time' => '2014-11-10 17:40:26',
                            'status' => 'sent',
                            'from_name' => 'Makar',
                            'from_email' => 'sichevoy@gmail.com',
                            'subject' => 'Test C',
                            'to_name' => '*|FNAME|*',
                            'archive_url' => 'http://eepurl.com/73jez',
                            'archive_url_long' => 'http://us9.campaign-archive1.com/?u=30a1fbd85fafe93f6446fef6e&id=4c34206f0b',
                            'emails_sent' => 2,
                            'inline_css' => false,
                            'analytics' => 'N',
                            'analytics_tag' => '',
                            'authenticate' => true,
                            'ecomm360' => false,
                            'auto_tweet' => false,
                            'auto_fb_post' => NULL,
                            'auto_footer' => false,
                            'timewarp' => false,
                            'timewarp_schedule' => NULL,
                            'tracking' =>
                                array (
                                    'html_clicks' => true,
                                    'text_clicks' => true,
                                    'opens' => true,
                                ),
                            'parent_id' => '',
                            'is_child' => false,
                            'tests_sent' => '0',
                            'tests_remain' => 12,
                            'segment_text' => '<ul id="conditions" class="conditions"><li class="nomargin"><span class="small-meta">Matching <strong>any</strong> conditions:</span></li><li>Static Segments member is part of <strong>Test ML<strong></li></ul><span>For a total of <strong>2</strong> emails sent.</span>',
                            'segment_opts' =>
                                array (
                                    'match' => 'any',
                                    'conditions' =>
                                        array (
                                            0 =>
                                                array (
                                                    'field' => 'static_segment',
                                                    'op' => 'eq',
                                                    'value' => 30261,
                                                ),
                                        ),
                                ),
                            'saved_segment' =>
                                array (
                                    'id' => 30261,
                                    'type' => 'static',
                                    'name' => 'Test ML',
                                ),
                            'type_opts' =>
                                array (
                                ),
                            'comments_total' => 0,
                            'comments_unread' => 0,
                            'summary' =>
                                array (
                                    'syntax_errors' => 0,
                                    'hard_bounces' => 0,
                                    'soft_bounces' => 0,
                                    'unsubscribes' => 0,
                                    'abuse_reports' => 0,
                                    'forwards' => 0,
                                    'forwards_opens' => 0,
                                    'opens' => 2,
                                    'last_open' => NULL,
                                    'unique_opens' => 0,
                                    'clicks' => 1,
                                    'unique_clicks' => 0,
                                    'users_who_clicked' => 0,
                                    'last_click' => NULL,
                                    'emails_sent' => 2,
                                    'unique_likes' => 0,
                                    'recipient_likes' => 0,
                                    'facebook_likes' => 0,
                                    'industry' =>
                                        array (
                                            'type' => 'Software and Web App',
                                            'open_rate' => 0.20547381614224999,
                                            'click_rate' => 0.024960592576843001,
                                            'bounce_rate' => 0.055762297273677999,
                                            'unopen_rate' => 0.73876388658407,
                                            'unsub_rate' => 0.0071395657266407004,
                                            'abuse_rate' => 0.00063851068202262003,
                                        ),
                                    'absplit' =>
                                        array (
                                        ),
                                    'timewarp' =>
                                        array (
                                        ),
                                    'timeseries' =>
                                        array (
                                            0 =>
                                                array (
                                                    'timestamp' => '2014-11-10 17:00:00',
                                                    'emails_sent' => 2,
                                                    'unique_opens' => 0,
                                                    'recipients_click' => 0,
                                                ),
                                        ),
                                ),
                            'social_card' => NULL,
                        ),
                    ],
                    'errors' => []
                ],
                'assertMethod' => 'assertEquals',
                'assertCount' => '2',
                'expectedContent' => [
                    'Run sync for "mailchimp1" integration.',
                    'Start processing "campaign" connector',
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
