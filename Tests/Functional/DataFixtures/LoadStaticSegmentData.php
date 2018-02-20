<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;

class LoadStaticSegmentData extends AbstractMailChimpFixture implements DependentFixtureInterface
{
    /**
     * @var array Segment configuration
     */
    protected $segmentData = [
        [
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'marketingList' => 'mailchimp:ml_one',
            'channel' => 'mailchimp:channel_1',
            'name' => 'Test',
            'sync_status' => '',
            'remote_remove' => '0',
            'reference' => 'mailchimp:segment_one',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();

        foreach ($this->segmentData as $data) {
            $entity = new StaticSegment();
            $entity->setOwner($organization);
            $data['marketingList'] = $this->getReference($data['marketingList']);
            $data['subscribersList'] = $this->getReference($data['subscribersList']);
            $data['channel'] = $this->getReference($data['channel']);
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
        return [LoadMarketingListData::class, LoadSubscribersListData::class];
    }
}
