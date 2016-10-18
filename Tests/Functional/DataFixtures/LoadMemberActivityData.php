<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MailChimpBundle\Entity\Campaign;
use Oro\Bundle\MailChimpBundle\Entity\MemberActivity;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;

class LoadMemberActivityData extends AbstractMailChimpFixture implements DependentFixtureInterface
{
    /**
     * @var array Channels configuration
     */
    protected $data = [
        [
            'action' => 'open',
            'email' => 'member1@example.com',
            'channel' => 'mailchimp:channel_1',
            'member' => 'mailchimp:member_one',
            'campaign' => 'mailchimp:campaign_one',
            'reference' => 'mailchimp:member_one:activity:open'
        ],
        [
            'action' => 'click',
            'email' => 'member1@example.com',
            'channel' => 'mailchimp:channel_1',
            'member' => 'mailchimp:member_one',
            'campaign' => 'mailchimp:campaign_one',
            'reference' => 'mailchimp:member_one:activity:click:1'
        ],
        [
            'action' => 'click',
            'email' => 'member1@example.com',
            'channel' => 'mailchimp:channel_1',
            'member' => 'mailchimp:member_one',
            'campaign' => 'mailchimp:campaign_one',
            'reference' => 'mailchimp:member_one:activity:click:2'
        ],
        [
            'action' => 'open',
            'email' => 'member2@example.com',
            'channel' => 'mailchimp:channel_1',
            'member' => 'mailchimp:member_two',
            'campaign' => 'mailchimp:campaign_one',
            'reference' => 'mailchimp:member_two:activity:open'
        ],
        [
            'action' => 'open',
            'email' => 'member2@example.com',
            'channel' => 'mailchimp:channel_1',
            'member' => 'mailchimp:member_two',
            'campaign' => 'mailchimp:campaign_2',
            'reference' => 'mailchimp:member_two:activity:open:cmp2'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var StaticSegment $staticSegment */
        $staticSegment = $this->getReference('mailchimp:segment_one');

        foreach ($this->data as $data) {
            $entity = new MemberActivity();
            $data['channel'] = $this->getReference($data['channel']);
            $data['member'] = $this->getReference($data['member']);
            /** @var Campaign $campaign */
            $campaign = $this->getReference($data['campaign']);
            $campaign->setStaticSegment($staticSegment);
            $campaign->getEmailCampaign()->setMarketingList($staticSegment->getMarketingList());
            $data['campaign'] = $campaign;
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
            __NAMESPACE__ . '\LoadMemberData',
            __NAMESPACE__ . '\LoadStaticSegmentData'
        ];
    }
}
