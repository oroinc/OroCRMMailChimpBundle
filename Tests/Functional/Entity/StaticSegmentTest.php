<?php
namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use OroCRM\Bundle\MailChimpBundle\Async\Topics;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadMarketingListData;
use OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadSubscribersListData;

/**
 * @dbIsolationPerTest
 */
class StaticSegmentTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient();
        $this->getMessageProducer()->clearTraces();
        $this->loadFixtures([LoadMarketingListData::class, LoadSubscribersListData::class]);
    }

    public function testShouldScheduleExportOnceStaticSegmentCreated()
    {
        $organization = $this->getEntityManager()->getRepository(Organization::class)->getFirst();

        $segment = new StaticSegment();
        $segment->setName('Test');
        $segment->setRemoteRemove(false);
        $segment->setSyncStatus(StaticSegment::STATUS_NOT_SYNCED);
        $segment->setOwner($organization);
        $segment->setMarketingList($this->getReference('mailchimp:ml_one'));
        $segment->setSubscribersList($this->getReference('mailchimp:subscribers_list_one'));
        $segment->setChannel($this->getReference('mailchimp:channel_1'));

        $this->getEntityManager()->persist($segment);
        $this->getEntityManager()->flush();

        $traces = $this->getMessageProducer()->getTopicTraces(Topics::EXPORT_MAIL_CHIMP_SEGMENTS);
        self::assertCount(1, $traces);

        self::assertEquals([
            'integrationId' => $segment->getChannel()->getId(),
            'segmentsIds' => [$segment->getId()],
        ], $traces[0]['message']);
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['priority']);
    }

    /**
     * @return TraceableMessageProducer
     */
    private function getMessageProducer()
    {
        return self::getContainer()->get('oro_message_queue.message_producer');
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
