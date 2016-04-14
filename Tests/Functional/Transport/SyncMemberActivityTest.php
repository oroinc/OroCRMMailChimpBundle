<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Transport;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClientFactory;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class SyncMemberActivityTest extends WebTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $apiClient;

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
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $this->markTestSkipped('Due to BAP-10174');
        }

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
                ['export', 'getLists', 'getListMergeVars', 'getListStaticSegments']
            )
            ->getMock();
        $this->clientFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->apiClient));

        $this->entityBody = $this->getMockBuilder('Guzzle\Http\EntityBody\EntityBody')
            ->disableOriginalConstructor()
            ->setMethods(
                ['seek', 'readLine']
            )
            ->getMock();
        $this->response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $this->apiClient->expects($this->any())
            ->method('export')
            ->will($this->returnValue($this->response));
        $this->response->expects($this->any())
            ->method('isSuccessful')
            ->will($this->returnValue(true));
        $this->response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($this->entityBody));

        $transport = new MailChimpTransport($this->clientFactory, $this->getContainer()->get('doctrine'));
        $this->getContainer()->set('orocrm_mailchimp.transport.integration_transport', $transport);
        $this->loadFixtures(['OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadMemberData']);
    }

    /**
     * @dataProvider commandMemberActivityOptionsProvider
     * @param string $commandName
     * @param array $params
     * @param string $entity
     * @param array $data
     * @param string $assertMethod
     * @param int $assertCount
     * @param array $expectedList
     */
    public function testSyncMemberActivity(
        $commandName,
        array $params,
        $entity,
        $data,
        $assertMethod,
        $assertCount,
        $expectedList
    ) {
        $this->entityBody->expects($this->at(1))
            ->method('readLine')
            ->will($this->returnValue($data['line1_data']));
        $this->entityBody->expects($this->at(2))
            ->method('readLine')
            ->will($this->returnValue($data['line2_data']));

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
    public function commandMemberActivityOptionsProvider()
    {
        return [
            'SubscribersMemberActivitySyncCommand' => [
                'commandName' => 'oro:cron:integration:sync',
                'params' => ['--integration-id' => '1', '--connector' => 'member_activity'],
                'entity' => 'MemberActivity',
                'data' => [
                    'line1_data' => '{"member1@example.com":[{"action":"open","timestamp":"2014-11-12 11:00:02",
                        "url":null,"ip":"80.91.180.166"}]}',
                    'line2_data' => '{"member2@example.com":[{"action":"open","timestamp":"2014-11-12 11:00:01",
                        "url":null,"ip":"80.91.180.166"},
                        {"action":"click","timestamp":"2014-11-12 11:00:26","url":"http:\/\/inspiration.mailchimp.com",
                        "ip":"80.91.180.166"}]}'
                ],
                'assertMethod' => 'assertEquals',
                'assertCount' => '3',
                'expectedContent' => [
                    'Run sync for "mailchimp1" integration.',
                    'Start processing "member_activity" connector',
                    'invalid entities: [0]',
                    'processed [3]',
                    'deleted [0]',
                    'updated [0]',
                    'read [3]',
                    'added [3]',
                ]
            ],

        ];
    }
}
