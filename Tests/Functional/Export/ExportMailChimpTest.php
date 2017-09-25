<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\Export;

use Doctrine\Common\Cache\Cache;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Bundle\MailChimpBundle\Provider\Connector\StaticSegmentConnector;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;
use Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadB2bChannelData;
use Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentB2bCustomerData;
use Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadChannelData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class ExportMailChimpTest extends WebTestCase
{
    /**
     * @var ReverseSyncProcessor
     */
    protected $reverseSyncProcessor;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->initClient();

        $this->loadFixtures([
            LoadB2bChannelData::class, // add new chanel what uses B2bCustomer entity.
            LoadChannelData::class,
            LoadStaticSegmentB2bCustomerData::class
        ]);

        $this
            ->getContainer()
            ->set(
                'oro_mailchimp.transport.integration_transport',
                $this->createMock(MailChimpTransport::class)
            );

        /**
         * @var ReverseSyncProcessor
         */
        $this->reverseSyncProcessor = $this
            ->getContainer()
            ->get('oro_integration.reverse_sync.processor');
    }

    public function testExportSegmentsSeveralTimesWithinOneRequest()
    {
        // we should mock the oro_channel.state_cache service because
        //it does not update the real cache files during testing because of dbIsolationPerTest annotation
        $cache = $this->createMock(Cache::class);
        static::$kernel->getContainer()->set('oro_channel.state_cache', $cache);
        $cache->expects($this->any())
            ->method('fetch')
            ->willReturn(false);
        $cache->expects($this->any())
            ->method('save');

        $parameters =  [
            'segmentsIds' => [
                $this->getReference('mailchimp:segment_b2b')->getId(),
            ]
        ];
        $this->assertTrue(
            $this->reverseSyncProcessor->process(
                $this->getReference('mailchimp:channel_1'),
                StaticSegmentConnector::TYPE,
                $parameters
            )
        );
        $this->assertTrue(
            $this->reverseSyncProcessor->process(
                $this->getReference('mailchimp:channel_1'),
                StaticSegmentConnector::TYPE,
                $parameters
            )
        );
    }
}
