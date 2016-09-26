<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class LoadMarketingListData extends AbstractMailChimpFixture implements DependentFixtureInterface
{
    /**
     * @var array Channels configuration
     */
    protected $mlData = [
        [
            'type' => 'dynamic',
            'name' => 'Test ML',
            'description' => '',
            'entity' => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
            'reference' => 'mailchimp:ml_one',
            'segment' => 'mailchimp:ml_one:segment',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->mlData as $data) {
            $entity = new MarketingList();
            $type = $manager
                ->getRepository('OroCRMMarketingListBundle:MarketingListType')
                ->find($data['type']);
            $segment = $this->getReference($data['segment']);
            $entity->setType($type);
            $entity->setSegment($segment);
            $this->setEntityPropertyValues($entity, $data, ['reference', 'type', 'segment']);
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
            __NAMESPACE__ . '\LoadSegmentData',
            __NAMESPACE__ . '\LoadContactData',
        ];
    }
}
