<?php
namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use OroCRM\Bundle\MailChimpBundle\Async\Topics;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData;

/**
 * @dbIsolationPerTest
 */
class MailChimpExportCommandTest extends WebTestCase
{
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

        $traces = $this->getMessageProducer()->getTopicTraces(Topics::EXPORT_MAIL_CHIMP_SEGMENTS);

        $this->assertCount(1, $traces);

        $this->assertEquals([
            'integrationId' => $segment->getChannel()->getId(),
            'segmentsIds' => [$segment->getId()],
        ], $traces[0]['message']);
        $this->assertEquals(MessagePriority::VERY_LOW, $traces[0]['priority']);
    }

    /**
     * @return TraceableMessageProducer
     */
    private function getMessageProducer()
    {
        return $this->getContainer()->get('oro_message_queue.message_producer');
    }
}
