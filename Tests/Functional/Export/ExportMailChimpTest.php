<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\Export;

use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Bundle\MailChimpBundle\Provider\Connector\StaticSegmentConnector;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;
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
