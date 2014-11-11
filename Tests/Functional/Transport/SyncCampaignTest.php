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

        $this->entityBody = $this->getMockBuilder('Guzzle\Http\EntityBody\EntityBody')
            ->disableOriginalConstructor()
            ->setMethods(
                ['seek', 'readLine']
            )
            ->getMock();
        $this->response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $this->client->expects($this->any())
            ->method('export')
            ->will($this->returnValue($this->response));
        $this->response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($this->entityBody));

        $transport = new MailChimpTransport($this->clientFactory, $this->getContainer()->get('doctrine'));
        $this->getContainer()->set('orocrm_mailchimp.transport.integration_transport', $transport);
        $this->loadFixtures(['OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData']);
    }

    public function testSyncCampaign(
//        $commandName,
//        array $params,
//        $entity,
//        $data,
//        $assertMethod,
//        $assertCount,
//        $expectedList
    ) {
//        $commandName = 'oro:cron:integration:sync';
//        $params = ['--integration-id' => '1', '--connector' => 'campaign'];
//        $entity = 'Campaign';
//        $data = [
//            'total' => 2,
//            'data' => [
//                array (
//                    'id' => '4c34206f0b',
//                    'web_id' => 638833,
//                    'list_id' => '1a2925d57e',
//                    'folder_id' => 0,
//                    'template_id' => 91,
//                    'content_type' => 'template',
//                    'content_edited_by' => 'Oro CRM',
//                    'title' => 'Test C ML',
//                    'type' => 'regular',
//                    'create_time' => '2014-11-10 17:38:55',
//                    'send_time' => '2014-11-10 17:40:27',
//                    'content_updated_time' => '2014-11-10 17:40:26',
//                    'status' => 'sent',
//                    'from_name' => 'Makar',
//                    'from_email' => 'sichevoy@gmail.com',
//                    'subject' => 'Test C',
//                    'to_name' => '*|FNAME|*',
//                    'archive_url' => 'http://eepurl.com/73jez',
//                    'archive_url_long' => 'http://us9.campaign-archive1.com/?u=30a1fbd85fafe93f6446fef6e&id=4c34206f0b',
//                    'emails_sent' => 2,
//                    'inline_css' => false,
//                    'analytics' => 'N',
//                    'analytics_tag' => '',
//                    'authenticate' => true,
//                    'ecomm360' => false,
//                    'auto_tweet' => false,
//                    'auto_fb_post' => NULL,
//                    'auto_footer' => false,
//                    'timewarp' => false,
//                    'timewarp_schedule' => NULL,
//                    'tracking' =>
//                        array (
//                            'html_clicks' => true,
//                            'text_clicks' => true,
//                            'opens' => true,
//                        ),
//                    'parent_id' => '',
//                    'is_child' => false,
//                    'tests_sent' => '0',
//                    'tests_remain' => 12,
//                    'segment_text' => '<ul id="conditions" class="conditions"><li class="nomargin"><span class="small-meta">Matching <strong>any</strong> conditions:</span></li><li>Static Segments member is part of <strong>Test ML<strong></li></ul><span>For a total of <strong>2</strong> emails sent.</span>',
//                    'segment_opts' =>
//                        array (
//                            'match' => 'any',
//                            'conditions' =>
//                                array (
//                                    0 =>
//                                        array (
//                                            'field' => 'static_segment',
//                                            'op' => 'eq',
//                                            'value' => 30261,
//                                        ),
//                                ),
//                        ),
//                    'saved_segment' =>
//                        array (
//                            'id' => 30261,
//                            'type' => 'static',
//                            'name' => 'Test ML',
//                        ),
//                    'type_opts' =>
//                        array (
//                        ),
//                    'comments_total' => 0,
//                    'comments_unread' => 0,
//                    'summary' =>
//                        array (
//                            'syntax_errors' => 0,
//                            'hard_bounces' => 0,
//                            'soft_bounces' => 0,
//                            'unsubscribes' => 0,
//                            'abuse_reports' => 0,
//                            'forwards' => 0,
//                            'forwards_opens' => 0,
//                            'opens' => 0,
//                            'last_open' => NULL,
//                            'unique_opens' => 0,
//                            'clicks' => 0,
//                            'unique_clicks' => 0,
//                            'users_who_clicked' => 0,
//                            'last_click' => NULL,
//                            'emails_sent' => 2,
//                            'unique_likes' => 0,
//                            'recipient_likes' => 0,
//                            'facebook_likes' => 0,
//                            'industry' =>
//                                array (
//                                    'type' => 'Software and Web App',
//                                    'open_rate' => 0.20547381614224999,
//                                    'click_rate' => 0.024960592576843001,
//                                    'bounce_rate' => 0.055762297273677999,
//                                    'unopen_rate' => 0.73876388658407,
//                                    'unsub_rate' => 0.0071395657266407004,
//                                    'abuse_rate' => 0.00063851068202262003,
//                                ),
//                            'absplit' =>
//                                array (
//                                ),
//                            'timewarp' =>
//                                array (
//                                ),
//                            'timeseries' =>
//                                array (
//                                    0 =>
//                                        array (
//                                            'timestamp' => '2014-11-10 17:00:00',
//                                            'emails_sent' => 2,
//                                            'unique_opens' => 0,
//                                            'recipients_click' => 0,
//                                        ),
//                                ),
//                        ),
//                    'social_card' => NULL,
//                ),
//                array (
//                    'id' => '4134206f0b',
//                    'web_id' => 618833,
//                    'list_id' => '112925d57e',
//                    'folder_id' => 0,
//                    'template_id' => 91,
//                    'content_type' => 'template',
//                    'content_edited_by' => 'Oro CRM',
//                    'title' => 'Test C ML',
//                    'type' => 'regular',
//                    'create_time' => '2014-11-10 17:38:55',
//                    'send_time' => '2014-11-10 17:40:27',
//                    'content_updated_time' => '2014-11-10 17:40:26',
//                    'status' => 'sent',
//                    'from_name' => 'Makar',
//                    'from_email' => 'sichevoy@gmail.com',
//                    'subject' => 'Test C',
//                    'to_name' => '*|FNAME|*',
//                    'archive_url' => 'http://eepurl.com/73jez',
//                    'archive_url_long' => 'http://us9.campaign-archive1.com/?u=30a1fbd85fafe93f6446fef6e&id=4c34206f0b',
//                    'emails_sent' => 2,
//                    'inline_css' => false,
//                    'analytics' => 'N',
//                    'analytics_tag' => '',
//                    'authenticate' => true,
//                    'ecomm360' => false,
//                    'auto_tweet' => false,
//                    'auto_fb_post' => NULL,
//                    'auto_footer' => false,
//                    'timewarp' => false,
//                    'timewarp_schedule' => NULL,
//                    'tracking' =>
//                        array (
//                            'html_clicks' => true,
//                            'text_clicks' => true,
//                            'opens' => true,
//                        ),
//                    'parent_id' => '',
//                    'is_child' => false,
//                    'tests_sent' => '0',
//                    'tests_remain' => 12,
//                    'segment_text' => '<ul id="conditions" class="conditions"><li class="nomargin"><span class="small-meta">Matching <strong>any</strong> conditions:</span></li><li>Static Segments member is part of <strong>Test ML<strong></li></ul><span>For a total of <strong>2</strong> emails sent.</span>',
//                    'segment_opts' =>
//                        array (
//                            'match' => 'any',
//                            'conditions' =>
//                                array (
//                                    0 =>
//                                        array (
//                                            'field' => 'static_segment',
//                                            'op' => 'eq',
//                                            'value' => 30261,
//                                        ),
//                                ),
//                        ),
//                    'saved_segment' =>
//                        array (
//                            'id' => 30261,
//                            'type' => 'static',
//                            'name' => 'Test ML',
//                        ),
//                    'type_opts' =>
//                        array (
//                        ),
//                    'comments_total' => 0,
//                    'comments_unread' => 0,
//                    'summary' =>
//                        array (
//                            'syntax_errors' => 0,
//                            'hard_bounces' => 0,
//                            'soft_bounces' => 0,
//                            'unsubscribes' => 0,
//                            'abuse_reports' => 0,
//                            'forwards' => 0,
//                            'forwards_opens' => 0,
//                            'opens' => 0,
//                            'last_open' => NULL,
//                            'unique_opens' => 0,
//                            'clicks' => 0,
//                            'unique_clicks' => 0,
//                            'users_who_clicked' => 0,
//                            'last_click' => NULL,
//                            'emails_sent' => 2,
//                            'unique_likes' => 0,
//                            'recipient_likes' => 0,
//                            'facebook_likes' => 0,
//                            'industry' =>
//                                array (
//                                    'type' => 'Software and Web App',
//                                    'open_rate' => 0.20547381614224999,
//                                    'click_rate' => 0.024960592576843001,
//                                    'bounce_rate' => 0.055762297273677999,
//                                    'unopen_rate' => 0.73876388658407,
//                                    'unsub_rate' => 0.0071395657266407004,
//                                    'abuse_rate' => 0.00063851068202262003,
//                                ),
//                            'absplit' =>
//                                array (
//                                ),
//                            'timewarp' =>
//                                array (
//                                ),
//                            'timeseries' =>
//                                array (
//                                    0 =>
//                                        array (
//                                            'timestamp' => '2014-11-10 17:00:00',
//                                            'emails_sent' => 2,
//                                            'unique_opens' => 0,
//                                            'recipients_click' => 0,
//                                        ),
//                                ),
//                        ),
//                    'social_card' => NULL,
//                ),
//            ],
//            'errors' => []
//        ];
//        $assertMethod = 'assertEquals';
//        $assertCount = '2';
//        $expectedList = ['Run sync for "mailchimp1" integration.'];
//
//        $this->client->expects($this->once())
//            ->method('getCampaigns')
//            ->will($this->returnValue($data));
//        $this->clientFactory->expects($this->any())
//            ->method('create')
//            ->will($this->returnValue($this->client));
//
//        if (isset($params['--integration-id'])) {
//            $params['--integration-id'] = (string)$this->getReference(
//                'mailchimp_transport:test_transport' . $params['--integration-id']
//            )->getId();
//        }
//        $result = $this->runCommand($commandName, $params);
//        foreach ($expectedList as $expected) {
//            $this->assertContains($expected, $result);
//        }
//        if ($assertMethod) {
//            $listRepo = $this->getContainer()->get('doctrine')->getRepository('OroCRMMailChimpBundle:' . $entity);
//            $list = $listRepo->findAll();
//            $this->$assertMethod($assertCount, count($list));
//        }
    }

}




/*
Campaign

array (
  'total' => 1,
  'data' =>
  array (
    0 =>
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
      'archive_url_long' => 'http://us9.campaign-archive2.com/?u=30a1fbd85fafe93f6446fef6e&id=4c34206f0b',
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
          'open_rate' => 0.20563408155825,
          'click_rate' => 0.025111070049774,
          'bounce_rate' => 0.055305539642032997,
          'unopen_rate' => 0.73906037879971997,
          'unsub_rate' => 0.0070828663014576004,
          'abuse_rate' => 0.00063206078000838995,
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
          1 =>
          array (
            'timestamp' => '2014-11-10 18:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          2 =>
          array (
            'timestamp' => '2014-11-10 19:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          3 =>
          array (
            'timestamp' => '2014-11-10 20:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          4 =>
          array (
            'timestamp' => '2014-11-10 21:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          5 =>
          array (
            'timestamp' => '2014-11-10 22:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          6 =>
          array (
            'timestamp' => '2014-11-10 23:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          7 =>
          array (
            'timestamp' => '2014-11-11 00:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          8 =>
          array (
            'timestamp' => '2014-11-11 01:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          9 =>
          array (
            'timestamp' => '2014-11-11 02:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          10 =>
          array (
            'timestamp' => '2014-11-11 03:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          11 =>
          array (
            'timestamp' => '2014-11-11 04:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          12 =>
          array (
            'timestamp' => '2014-11-11 05:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          13 =>
          array (
            'timestamp' => '2014-11-11 06:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          14 =>
          array (
            'timestamp' => '2014-11-11 07:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          15 =>
          array (
            'timestamp' => '2014-11-11 08:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          16 =>
          array (
            'timestamp' => '2014-11-11 09:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          17 =>
          array (
            'timestamp' => '2014-11-11 10:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          18 =>
          array (
            'timestamp' => '2014-11-11 11:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          19 =>
          array (
            'timestamp' => '2014-11-11 12:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          20 =>
          array (
            'timestamp' => '2014-11-11 13:00:00',
            'emails_sent' => 0,
            'unique_opens' => 0,
            'recipients_click' => 0,
          ),
          21 =>
          array (
            'timestamp' => '2014-11-11 14:00:00',
            'unique_opens' => 0,
            'recipients_click' => 0,
            'emails_sent' => 0,
          ),
          22 =>
          array (
            'timestamp' => '2014-11-11 15:00:00',
            'unique_opens' => 0,
            'recipients_click' => 0,
            'emails_sent' => 0,
          ),
        ),
      ),
      'social_card' => NULL,
    ),
  ),
  'errors' =>
  array (
  ),
)

 */