<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\MailChimpBundle\Entity\Campaign;
use Oro\Bundle\MailChimpBundle\Entity\MailChimpTransport as MailChimpTransportEntity;
use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\Form\Type\IntegrationSettingsType;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClientFactory;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;
use Psr\Log\NullLogger;

class MailChimpTransportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|MailChimpClientFactory
     */
    protected $clientFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var MailChimpTransport
     */
    protected $transport;

    protected function setUp()
    {
        $this->clientFactory = $this->getMockBuilder(
            'Oro\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClientFactory'
        )->disableOriginalConstructor()->getMock();

        $this->managerRegistry = $this->createMock('Doctrine\\Common\\Persistence\\ManagerRegistry');

        $this->transport = new MailChimpTransport($this->clientFactory, $this->managerRegistry);

        $this->transport->setLogger(new NullLogger());
    }

    public function testGetSettingsEntityFQCN()
    {
        $this->assertInstanceOf($this->transport->getSettingsEntityFQCN(), new MailChimpTransportEntity());
    }

    public function testGetLabel()
    {
        $this->assertEquals('oro.mailchimp.integration_transport.label', $this->transport->getLabel());
    }

    public function testGetSettingsFormType()
    {
        $this->assertEquals(
            IntegrationSettingsType::class,
            $this->transport->getSettingsFormType()
        );
    }

    public function testInitWorks()
    {
        $client = $this->initTransport();

        $this->assertAttributeSame($client, 'client', $this->transport);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function initTransport()
    {
        $apiKey = md5(rand());

        $transportEntity = new MailChimpTransportEntity();
        $transportEntity->setApiKey($apiKey);

        $client = $this->getMockBuilder('Oro\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClient')
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
     * @expectedException \Oro\Bundle\MailChimpBundle\Exception\RequiredOptionException
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
        $staticSegmentRepository = $this
            ->getMockBuilder('Oro\\Bundle\\MailChimpBundle\\Entity\\Repository\\StaticSegmentRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerRegistry
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($staticSegmentRepository));

        $staticSegmentRepository
            ->expects($this->once())
            ->method('getStaticSegments')
            ->will($this->returnValue([$this->getStaticSegmentMock()]));

        $channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();

        $this->initTransport();
        $result = $this->transport->getCampaigns($channel, $status, $usesSegment);

        $this->assertInstanceOf(
            'Oro\\Bundle\\MailChimpBundle\\Provider\\Transport\\Iterator\\CampaignIterator',
            $result
        );

        $this->assertAttributeSame($expectedFilters, 'filters', $result);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getStaticSegmentMock()
    {
        $staticSegmentMock = $this
            ->getMockBuilder('Oro\\Bundle\\MailChimpBundle\\Entity\\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();

        $subscribersList = $this
            ->getMockBuilder('Oro\\Bundle\\MailChimpBundle\\Entity\\SubscribersList')
            ->disableOriginalConstructor()
            ->getMock();

        $staticSegmentMock
            ->expects($this->once())
            ->method('getSubscribersList')
            ->will($this->returnValue($subscribersList));

        $subscribersList
            ->expects($this->once())
            ->method('getOriginId')
            ->will($this->returnValue('originId'));

        return $staticSegmentMock;
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
                'filters' => [
                    'list_id' => 'originId',
                    'exact' => false,
                ],
            ],
            [
                'status' => Campaign::STATUS_SENT,
                'usesSegment' => null,
                'filters' => [
                    'status' => Campaign::STATUS_SENT,
                    'list_id' => 'originId',
                    'exact' => false,
                ],
            ],
            [
                'status' => null,
                'usesSegment' => true,
                'filters' => [
                    'uses_segment' => true,
                    'list_id' => 'originId',
                    'exact' => false,
                ],
            ],
            [
                'status' => Campaign::STATUS_SENT,
                'usesSegment' => true,
                'filters' => [
                    'status' => Campaign::STATUS_SENT,
                    'uses_segment' => true,
                    'list_id' => 'originId',
                    'exact' => false,
                ],
            ],
        ];
    }

    public function testGetMembersToSync()
    {
        $subscribersListRepository = $this
            ->getMockBuilder('Oro\\Bundle\\MailChimpBundle\\Entity\\Repository\\SubscribersListRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerRegistry->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($subscribersListRepository));

        $subscribersList = $this->createMock('Oro\\Bundle\\MailChimpBundle\\Entity\\SubscribersList');
        $subscribersLists = new \ArrayIterator([$subscribersList]);

        $subscribersListRepository->expects($this->once())
            ->method('getUsedSubscribersListIterator')
            ->will($this->returnValue($subscribersLists));

        $since = new \DateTime('2015-02-15 21:00:01', new \DateTimeZone('Europe/Kiev'));

        $channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->initTransport();
        $result = $this->transport->getMembersToSync($channel, $since);

        $this->assertInstanceOf(
            'Oro\\Bundle\\MailChimpBundle\\Provider\\Transport\\Iterator\\MemberIterator',
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
