<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\Command;

use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use Oro\Bundle\MailChimpBundle\Async\Topics;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData;

/**
 * @dbIsolationPerTest
 */
class MailChimpExportCommandTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp()
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadStaticSegmentData::class]);
    }

    public function testShouldOutputHelpForTheCommand()
    {
        $result = $this->runCommand('oro:cron:mailchimp:export', ['--help']);

        $this->assertContains("Usage:\n  oro:cron:mailchimp:export [options]", $result);
    }

    public function testShouldSendExportMailChimpSegmentsMessage()
    {
        /** @var StaticSegment $segment */
        $segment = $this->getReference('mailchimp:segment_one');

        $result = $this->runCommand('oro:cron:mailchimp:export', ['--segments='.$segment->getId()]);

        $this->assertContains('Send export mail chimp message for channel:', $result);
        $this->assertContains(
            'Channel "'.$segment->getChannel()->getId().'" and segments "'.$segment->getId().'"',
            $result
        );

        $this->assertContains('Completed', $result);

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::EXPORT_MAIL_CHIMP_SEGMENTS);

        $this->assertCount(1, $traces);

        $this->assertEquals([
            'integrationId' => $segment->getChannel()->getId(),
            'segmentsIds' => [$segment->getId()],
        ], $traces[0]['message']->getBody());
        $this->assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    /**
     * @return TraceableMessageProducer
     */
    private function getMessageProducer()
    {
        return $this->getContainer()->get('oro_message_queue.message_producer');
    }
}
