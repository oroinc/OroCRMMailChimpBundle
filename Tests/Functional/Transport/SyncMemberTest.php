<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Transport;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClientFactory;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class SyncMemberTest extends WebTestCase
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
            ->method('getBody')
            ->will($this->returnValue($this->entityBody));

        $transport = new MailChimpTransport($this->clientFactory, $this->getContainer()->get('doctrine'));
        $this->getContainer()->set('orocrm_mailchimp.transport.integration_transport', $transport);
        $this->loadFixtures(['OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData']);
    }

    /**
     * @dataProvider commandMemberOptionsProvider
     * @param string $commandName
     * @param array $params
     * @param string $entity
     * @param array $data
     * @param string $assertMethod
     * @param int $assertCount
     * @param array $expectedList
     */
    public function testSyncMember(
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
            ->will($this->returnValue($data['line1']));
        $this->entityBody->expects($this->at(2))
            ->method('readLine')
            ->will($this->returnValue($data['line1_data']));
        $this->entityBody->expects($this->at(3))
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
    public function commandMemberOptionsProvider()
    {
        return [
            'StaticMemberCommand' => [
                'commandName'     => 'oro:cron:integration:sync',
                'params'          => ['--integration-id' => '1', '--connector' => 'member'],
                'entity'          => 'Member',
                'data'            => [
                    'line1' => '["Email Address","First Name","Last Name","MEMBER_RATING","OPTIN_TIME","OPTIN_IP",
                        "CONFIRM_TIME","CONFIRM_IP","LATITUDE","LONGITUDE","GMTOFF","DSTOFF","TIMEZONE","CC","REGION",
                        "LAST_CHANGED","LEID","EUID","NOTES"]',
                    'line1_data' => '["member1@example.com","Test","Test",2,"",null,"2014-11-11 15:46:02",
                        "80.91.180.166",null,null,null,null,null,null,null,"2014-11-11 15:46:02","213152069",
                        "ff337f0cf3",null]',
                    'line2_data' => '["member2@example.com","Test2","Test2",2,"",null,"2014-11-11 15:45:39",
                        "80.91.180.166",null,null,null,null,null,null,null,"2014-11-11 15:45:39","213152065",
                        "2a7e4cd76a",null]',
                ],
                'assertMethod'    => 'assertEquals',
                'assertCount'     => '2',
                'expectedContent' => [
                    'Run sync for "mailchimp1" integration.',
                    'Start processing "member" connector',
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
