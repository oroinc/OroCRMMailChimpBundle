<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\Entity;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MailChimpBundle\Entity\MarketingListEmail;
use Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadMarketingListEmailData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ChannelListenerTest extends WebTestCase
{
    use MessageQueueExtension;

    public function setUp()
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadMarketingListEmailData::class]);
    }

    public function testShouldRemoveRelatedMarketingListEmailsOnChannelRemoval()
    {
        /** @var Registry $registry */
        $registry = self::getContainer()->get('doctrine');
        $channelManager = $registry->getManagerForClass(Channel::class);

        $channel = $this->getReference('mailchimp:channel_1');
        $channelManager->remove($channel);

        $emails = $registry
            ->getManagerForClass(MarketingListEmail::class)
            ->getRepository(MarketingListEmail::class)->findAll();

        self::assertEmpty($emails);
    }
}
