<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MailChimpBundle\Entity\MarketingListEmail;

class LoadMarketingListEmailData extends AbstractMailChimpFixture implements DependentFixtureInterface
{
    /**
     * @var array Segment configuration
     */
    protected $segmentData = [
        [
            'marketingList' => 'mailchimp:ml_one',
            'email' => 'test@example.com',
            'state' => 'in_list',
            'reference' => 'mailchimp:ml_email_one'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->segmentData as $data) {
            $entity = new MarketingListEmail();
            $data['marketingList'] = $this->getReference($data['marketingList']);
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
        return [LoadStaticSegmentData::class];
    }
}
