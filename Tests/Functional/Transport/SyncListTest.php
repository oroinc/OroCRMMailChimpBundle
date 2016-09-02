<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Transport;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
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
    protected $apiClient;

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
        $this->apiClient = $this->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient')
            ->disableOriginalConstructor()
            ->setMethods(
                ['export', 'getLists', 'getListMergeVars']
            )
            ->getMock();
        $this->clientFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->apiClient));

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
        $this->apiClient->expects($this->once())
            ->method($mockMethod)
            ->will($this->returnValue($data));

        if (isset($params['--integration'])) {
            $params['--integration'] = (string)$this->getReference(
                'mailchimp:channel_' . $params['--integration']
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
    public function commandListOptionsProvider()
    {
        $results = [];
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'listResponses';
        $apiData = $this->getApiRequestsData($path);

        foreach ($apiData as $test => $data) {
            $results[$test] = [
                'commandName' => 'oro:cron:integration:sync',
                'params' => ['--integration' => '1', '--connector' => 'list'],
                'mockMethod' => 'getLists',
                'entity' => 'SubscribersList',
                'data' => $data['response'],
                'assertMethod' => 'assertEquals',
                'assertCount' => count($data['response']['data']),
                'expectedContent' => [
                    'Run sync for "mailchimp1" integration.'
                ]
            ];
        }

        return $results;
    }
}
