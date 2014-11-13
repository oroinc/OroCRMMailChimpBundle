<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Transport;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClientFactory;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class SyncStaticSegmentTest extends WebTestCase
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

        $transport = new MailChimpTransport($this->clientFactory, $this->getContainer()->get('doctrine'));
        $this->getContainer()->set('orocrm_mailchimp.transport.integration_transport', $transport);
        $this->loadFixtures(['OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData']);
    }

    /**
     * @dataProvider commandStaticSegmentOptionsProvider
     * @param string $commandName
     * @param array $params
     * @param string $mockMethod
     * @param string $entity
     * @param array $data
     * @param string $assertMethod
     * @param int $assertCount
     * @param array $expectedList
     */
    public function testSyncStaticSegment(
        $commandName,
        array $params,
        $mockMethod,
        $entity,
        $data,
        $assertMethod,
        $assertCount,
        $expectedList
    ) {
        $this->apiClient->expects($this->once())
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
     * @return array
     */
    public function commandStaticSegmentOptionsProvider()
    {
        return [
            'StaticSegmentSyncCommand' => [
                'commandName' => 'oro:cron:integration:sync',
                'params' => ['--integration-id' => '1', '--connector' => 'static_segment'],
                'mockMethod' => 'getListStaticSegments',
                'entity' => 'StaticSegment',
                'data' => [
                    [
                        'id' => 30261,
                        'name' => 'Test ML',
                        'created_date' => '2014-11-10 16:53:55',
                        'last_update' => '2014-11-10 16:53:56',
                        'last_reset' => null,
                        'member_count' => 2,
                    ],
                    [
                        'id' => 30262,
                        'name' => 'Test ML 2',
                        'created_date' => '2014-11-10 16:53:55',
                        'last_update' => '2014-11-10 16:53:56',
                        'last_reset' => null,
                        'member_count' => 4,
                    ]
                ],
                'assertMethod' => 'assertEquals',
                'assertCount' => '1',
                'expectedContent' => [
                    'Run sync for "mailchimp1" integration.',
                    'Start processing "static_segment" connector',
                    'invalid entities: [0]',
                    'process [0]',
                    'delete [0]',
                    'updated [0]',
                    'read [2]',
                    'added [0]',
                ]
            ],
        ];
    }
}
