<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransport as MailChimpTransportEntity;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

class MailChimpTransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var MailChimpTransport
     */
    protected $transport;

    protected function setUp()
    {
        $this->markTestIncomplete();

        $this->clientFactory = $this->getMockBuilder(
            'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClientFactory'
        )->disableOriginalConstructor()->getMock();

        $this->managerRegistry = $this->getMock('Doctrine\\Common\\Persistence\\ManagerRegistry');

        $this->transport = new MailChimpTransport($this->clientFactory, $this->managerRegistry);
    }

    public function testGetSettingsEntityFQCN()
    {
        $this->assertInstanceOf($this->transport->getSettingsEntityFQCN(), new MailChimpTransportEntity());
    }

    public function testGetLabel()
    {
        $this->assertEquals('orocrm.mailchimp.integration_transport.label', $this->transport->getLabel());
    }

    public function testGetSettingsFormType()
    {
        $this->assertEquals(
            'orocrm_mailchimp_integration_transport_setting_type',
            $this->transport->getSettingsFormType()
        );
    }

    public function testInitWorks()
    {
        $client = $this->initTransport();

        $this->assertAttributeSame($client, 'client', $this->transport);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function initTransport()
    {
        $apiKey = md5(rand());

        $transportEntity = new MailChimpTransportEntity();
        $transportEntity->setApiKey($apiKey);

        $client = $this->getMockBuilder('OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClient')
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientFactory->expects($this->once())
            ->method('create')
            ->with($apiKey)
            ->will($this->returnValue($client));

        $this->transport->init($transportEntity);

        return $client;
    }

    /**
     * @expectedException \OroCRM\Bundle\MailChimpBundle\Exception\RequiredOptionException
     * @expectedExceptionMessage Option "apiKey" is required
     */
    public function testInitFails()
    {
        $transportEntity = new MailChimpTransportEntity();

        $this->clientFactory->expects($this->never())->method($this->anything());
        $this->transport->init($transportEntity);
    }

    /**
     * @dataProvider getCampaignsDataProvider
     *
     * @param string|null $status
     * @param bool|null $usesSegment
     * @param array $expectedFilters
     */
    public function testGetCampaigns($status, $usesSegment, array $expectedFilters)
    {
        $this->initTransport();
        $result = $this->transport->getCampaigns($status, $usesSegment);

        $this->assertInstanceOf(
            'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\Iterator\\CampaignIterator',
            $result
        );

        $this->assertAttributeSame($expectedFilters, 'filters', $result);
    }

    /**
     * @return array
     */
    public function getCampaignsDataProvider()
    {
        return [
            [
                'status' => null,
                'usesSegment' => null,
                'filters' => [],
            ],
            [
                'status' => Campaign::STATUS_SENT,
                'usesSegment' => null,
                'filters' => [
                    'status' => Campaign::STATUS_SENT
                ],
            ],
            [
                'status' => null,
                'usesSegment' => true,
                'filters' => [
                    'uses_segment' => true
                ],
            ],
            [
                'status' => Campaign::STATUS_SENT,
                'usesSegment' => true,
                'filters' => [
                    'status' => Campaign::STATUS_SENT,
                    'uses_segment' => true
                ],
            ],
        ];
    }

    public function testGetMembersToSync()
    {
        $subscribersListRepository = $this->getMockBuilder(
            'OroCRM\\Bundle\\MailChimpBundle\\Entity\\Repository\SubscribersListRepository'
        )->disableOriginalConstructor()->getMock();

        $this->managerRegistry->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($subscribersListRepository));

        $subscribersList = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\SubscribersList');
        $subscribersLists = new \ArrayIterator([$subscribersList]);

        $subscribersListRepository->expects($this->once())
            ->method('getAllSubscribersListIterator')
            ->will($this->returnValue($subscribersLists));

        $since = new \DateTime('2015-02-15 21:00:01', new \DateTimeZone('Europe/Kiev'));

        $client = $this->initTransport();
        $result = $this->transport->getMembersToSync($since);

        $this->assertInstanceOf(
            'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\Iterator\\MemberIterator',
            $result
        );

        $this->assertAttributeSame($client, 'client', $result);
        $this->assertAttributeSame($subscribersLists, 'mainIterator', $result);
        $this->assertAttributeEquals(
            [
                'status' => [Member::STATUS_SUBSCRIBED, Member::STATUS_UNSUBSCRIBED, Member::STATUS_CLEANED],
                'since' => '2015-02-15 19:00:00',
            ],
            'parameters',
            $result
        );
    }
}
