<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransport;
use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings;

class CampaignTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Campaign
     */
    protected $target;

    public function setUp()
    {
        $this->target = new Campaign();
    }

    /**
     * @param string $property
     * @param mixed $value
     *
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $method = 'set' . ucfirst($property);
        $result = $this->target->$method($value);

        $this->assertInstanceOf(get_class($this->target), $result);
        $this->assertEquals($value, $this->target->{'get' . $property}());
    }

    /**
     * @return array
     */
    public function settersAndGettersDataProvider()
    {
        return [
            ['originId', 123456789],
            ['channel', $this->getMock('Oro\\Bundle\\IntegrationBundle\\Entity\\Channel')],
            ['title', 'Test title'],
            ['subject', 'Test subject'],
            ['fromName', 'John Doe'],
            ['fromEmail', 'text@example.com'],
            ['owner', $this->getMock('Oro\\Bundle\\OrganizationBundle\\Entity\\Organization')],
            ['webId', 123425223],
            ['template', $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\Template')],
            ['subscribersList', $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\SubscribersList')],
            ['staticSegment', $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\StaticSegment')],
            ['emailCampaign', $this->getMock('Oro\\Bundle\\CampaignBundle\\Entity\\EmailCampaign')],
            ['contentType', 'Content Type'],
            ['contentType', null],
            ['type', 'Type'],
            ['type', null],
            ['status', Campaign::STATUS_SENT],
            ['sendTime', new \DateTime()],
            ['sendTime', null],
            ['lastOpenDate', new \DateTime()],
            ['lastOpenDate', null],
            ['archiveUrl', 'http://url/'],
            ['archiveUrl', null],
            ['archiveUrlLong', 'http://url/'],
            ['archiveUrlLong', null],
            ['emailsSent', 32],
            ['emailsSent', null],
            ['testsSent', 3],
            ['testsSent', null],
            ['testsRemain', 1],
            ['testsRemain', null],
            ['syntaxErrors', 1],
            ['syntaxErrors', null],
            ['hardBounces', 23],
            ['hardBounces', null],
            ['softBounces', 32],
            ['softBounces', null],
            ['unsubscribes', 12],
            ['unsubscribes', null],
            ['abuseReports', 4],
            ['abuseReports', null],
            ['forwards', 3],
            ['forwards', null],
            ['forwardsOpens', 7],
            ['forwardsOpens', null],
            ['opens', 3],
            ['opens', null],
            ['uniqueOpens', 3],
            ['uniqueOpens', null],
            ['clicks', 3],
            ['clicks', null],
            ['uniqueClicks', 3],
            ['uniqueClicks', null],
            ['usersWhoClicked', 3],
            ['usersWhoClicked', null],
            ['uniqueLikes', 3],
            ['uniqueLikes', null],
            ['recipientLikes', 3],
            ['recipientLikes', null],
            ['facebookLikes', 3],
            ['facebookLikes', null],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['updatedAt', null],
        ];
    }

    public function testPrePersist()
    {
        $this->assertNull($this->target->getCreatedAt());
        $this->assertNull($this->target->getUpdatedAt());

        $this->target->prePersist();

        $this->assertInstanceOf('\DateTime', $this->target->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->target->getUpdatedAt());

        $expectedCreated = $this->target->getCreatedAt();
        $expectedUpdated = $this->target->getUpdatedAt();

        $this->target->prePersist();

        $this->assertSame($expectedCreated, $this->target->getCreatedAt());
        $this->assertSame($expectedUpdated, $this->target->getUpdatedAt());
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->target->getUpdatedAt());
        $this->target->preUpdate();
        $this->assertInstanceOf('\DateTime', $this->target->getUpdatedAt());
    }

    /**
     * @dataProvider activityUpdateStateDataProvider
     * @param EmailCampaign|null $emailCampaign
     * @param \DateTime|null $sendTime
     * @param int $activityUpdateInterval
     * @param string $expected
     */
    public function testGetActivityUpdateState(
        $emailCampaign,
        $sendTime,
        $activityUpdateInterval,
        $expected
    ) {
        $this->target->setEmailCampaign($emailCampaign);
        $this->target->setSendTime($sendTime);

        $transport = new MailChimpTransport();
        $transport->setActivityUpdateInterval($activityUpdateInterval);
        $channel = new Channel();
        $channel->setTransport($transport);
        $this->target->setChannel($channel);

        $this->assertEquals($expected, $this->target->getActivityUpdateState());
    }

    /**
     * @return array
     */
    public function activityUpdateStateDataProvider()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $threeMonthAgo = clone($now);
        $threeMonthAgo->sub(new \DateInterval('P3M'));

        $emailCampaignEnabled = new EmailCampaign();
        $transportSettings = new MailChimpTransportSettings();
        $transportSettings->setReceiveActivities(true);
        $emailCampaignEnabled->setTransportSettings($transportSettings);

        $emailCampaignDisabled = new EmailCampaign();
        $transportSettings = new MailChimpTransportSettings();
        $transportSettings->setReceiveActivities(false);
        $emailCampaignDisabled->setTransportSettings($transportSettings);

        return [
            [
                null,
                null,
                null,
                Campaign::ACTIVITY_ENABLED
            ],
            [
                null,
                $now,
                null,
                Campaign::ACTIVITY_ENABLED
            ],
            [
                null,
                $threeMonthAgo,
                10,
                Campaign::ACTIVITY_EXPIRED
            ],
            [
                null,
                $threeMonthAgo,
                null,
                Campaign::ACTIVITY_ENABLED
            ],
            [
                $emailCampaignEnabled,
                $now,
                10,
                Campaign::ACTIVITY_ENABLED
            ],
            [
                $emailCampaignEnabled,
                $threeMonthAgo,
                10,
                Campaign::ACTIVITY_EXPIRED
            ],
            [
                $emailCampaignDisabled,
                $now,
                10,
                Campaign::ACTIVITY_DISABLED
            ],
            [
                $emailCampaignDisabled,
                $threeMonthAgo,
                10,
                Campaign::ACTIVITY_DISABLED
            ],
        ];
    }
}
