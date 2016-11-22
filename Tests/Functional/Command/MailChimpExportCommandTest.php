<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\Command;

use Oro\Bundle\MailChimpBundle\Async\Topics;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

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

        $this->assertContains('Send export MailChimp message for channel:', $result);
        $this->assertContains(
            'Channel "'.$segment->getChannel()->getId().'" and segments "'.$segment->getId().'"',
            $result
        );

        $this->assertContains('Completed', $result);

        self::assertMessageSent(
            Topics::EXPORT_MAILCHIMP_SEGMENTS,
            new Message(
                [
                    'integrationId' => $segment->getChannel()->getId(),
                    'segmentsIds' => [$segment->getId()],
                ],
                MessagePriority::VERY_LOW
            )
        );
    }
}
