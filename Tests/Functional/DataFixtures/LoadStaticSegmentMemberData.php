<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class LoadStaticSegmentMemberData extends AbstractMailChimpFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $data = [
        [
            'member' => 'mailchimp:member',
            'segment' => 'mailchimp:segment_one',
            'state' => StaticSegmentMember::STATE_SYNCED,
            'reference' => 'mailchimp:static-segment-member',
        ],
        [
            'member' => 'mailchimp:member2',
            'segment' => 'mailchimp:segment_one',
            'state' => StaticSegmentMember::STATE_SYNCED,
            'reference' => 'mailchimp:static-segment-member2',
        ],
        [
            'member' => 'mailchimp:member3',
            'segment' => 'mailchimp:segment_one',
            'state' => StaticSegmentMember::STATE_TO_DROP,
            'reference' => 'mailchimp:static-segment-member3',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $entity = new StaticSegmentMember();

            $entity->setStaticSegment($this->getReference($data['segment']));
            $entity->setMember($this->getReference($data['member']));

            $this->setEntityPropertyValues($entity, $data, ['reference', 'segment', 'member']);
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
        return [LoadMemberExportData::class];
    }
}
