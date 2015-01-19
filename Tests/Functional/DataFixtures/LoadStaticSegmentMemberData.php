<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

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
        ]
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
            __NAMESPACE__ . '\LoadMemberExportData',
        ];
    }
}
