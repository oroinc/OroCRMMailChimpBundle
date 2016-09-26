<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\MailChimpBundle\Entity\Campaign;
use Oro\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings;
use Oro\Bundle\MailChimpBundle\Transport\MailChimpTransport;

class LoadCampaignData extends AbstractMailChimpFixture implements DependentFixtureInterface
{
    /**
     * @var array Channels configuration
     */
    protected $data = [
        [
            'originId' => 'campaign1',
            'webId' => '111',
            'status' => Campaign::STATUS_SENT,
            'title' => 'Test Campaign Title',
            'subject' => 'Test Campaign',
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'channel' => 'mailchimp:channel_1',
            'reference' => 'mailchimp:campaign_one',
        ],
        [
            'originId' => 'campaign2',
            'webId' => '112',
            'status' => Campaign::STATUS_SENT,
            'title' => 'Test Campaign Title',
            'subject' => 'Test Campaign',
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'channel' => 'mailchimp:channel_1',
            'reference' => 'mailchimp:campaign_2',
        ],
        [
            'originId' => 'campaign3',
            'webId' => '113',
            'status' => Campaign::STATUS_SENT,
            'title' => 'Test Campaign Title',
            'subject' => 'Test Campaign',
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'channel' => 'mailchimp:channel_1',
            'reference' => 'mailchimp:campaign_3',
        ],
        [
            'originId' => 'campaign4',
            'webId' => '114',
            'status' => Campaign::STATUS_SENT,
            'title' => 'Test Campaign Title',
            'subject' => 'Test Campaign',
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'channel' => 'mailchimp:channel_1',
            'reference' => 'mailchimp:campaign_4',
        ],
        [
            'originId' => 'campaign5',
            'webId' => '115',
            'status' => Campaign::STATUS_SENT,
            'title' => 'Test Campaign Title',
            'subject' => 'Test Campaign',
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'channel' => 'mailchimp:channel_1',
            'reference' => 'mailchimp:campaign_5',
        ],
        [
            'originId' => 'campaign6',
            'webID' => '116',
            'status' => Campaign::STATUS_SCHEDULE,
            'title' => 'Test Campaign Title',
            'subject' => 'Test Campaign',
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'channel' => 'mailchimp:channel_1',
            'reference' => 'mailchimp:campaign_6',
        ],

    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();

        foreach ($this->data as $data) {
            /** @var Channel $channel */
            $channel = $this->getReference($data['channel']);

            $transportSettings = new MailChimpTransportSettings();
            $transportSettings->setChannel($channel);
            $transportSettings->setReceiveActivities(true);
            $manager->persist($transportSettings);

            $emailCampaign = new EmailCampaign();
            $emailCampaign->setSchedule(EmailCampaign::SCHEDULE_MANUAL);
            $emailCampaign->setName($data['subject']);
            $emailCampaign->setTransport(MailChimpTransport::NAME);
            $emailCampaign->setTransportSettings($transportSettings);
            $manager->persist($emailCampaign);

            $entity = new Campaign();
            $entity->setOwner($organization);
            $entity->setEmailCampaign($emailCampaign);
            $entity->setSendTime(new \DateTime('now', new \DateTimeZone('UTC')));
            $data['subscribersList'] = $this->getReference($data['subscribersList']);
            $data['channel'] = $channel;
            $this->setEntityPropertyValues($entity, $data, ['reference']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            __NAMESPACE__ . '\LoadStaticSegmentData',
        ];
    }
}
