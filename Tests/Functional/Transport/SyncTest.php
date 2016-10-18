<?php
namespace Oro\Bundle\MailChimpBundle\Tests\Functional\Transport;

use Oro\Bundle\IntegrationBundle\Async\Topics;
use Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class SyncTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures([LoadStaticSegmentData::class]);
    }

    /**
     * @dataProvider provideConnectorName
     */
    public function testSyncCampaign($connectorName)
    {
        $params = ['--integration' => '1', '--connector' => $connectorName];
        $params['--integration'] = (string)$this->getReference(
            'mailchimp:channel_' . $params['--integration']
        )->getId();
        $result = $this->runCommand('oro:cron:integration:sync', $params);

        $this->assertContains('Run sync for "mailchimp1" integration.', $result);
        $this->assertContains('Completed', $result);

        $traces = self::getMessageCollector()->getTopicSentMessages(Topics::SYNC_INTEGRATION);

        self::assertCount(1, $traces);
        self::assertEquals([
            'integration_id' => $params['--integration'],
            'connector_parameters' => [],
            'connector' => $connectorName,
            'transport_batch_size' => 100
        ], $traces[0]['message']->getBody());
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    public function provideConnectorName()
    {
        return [
            ['campaign'],
            ['list'],
            ['member_activity'],
            ['member'],
            ['static_segment']
        ];
    }
}
