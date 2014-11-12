<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;

class LoadCampaignData extends AbstractMailChimpFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $data = [
        [
            'originId' => 'campaign1',
            'webID' => '111',
            'reference' => 'mailchimp_campaign1',
            'status' => Campaign::STATUS_SENT,
        ],
        [
            'originId' => 'campaign2',
            'webID' => '112',
            'reference' => 'mailchimp_campaign2',
            'status' => Campaign::STATUS_SENT,
        ],
        [
            'originId' => 'campaign3',
            'webID' => '113',
            'reference' => 'mailchimp_campaign3',
            'status' => Campaign::STATUS_SENT,
        ],
        [
            'originId' => 'campaign4',
            'webID' => '114',
            'reference' => 'mailchimp_campaign4',
            'status' => Campaign::STATUS_SENT,
        ],
//        [
//            'originId' => 'campaign5',
//            'webID' => '115',
//            'reference' => 'mailchimp_campaign5',
//            'status' => Campaign::STATUS_SENT,
//        ],
//        [
//            'originId' => 'campaign6',
//            'webID' => '116',
//            'reference' => 'mailchimp_campaign6',
//            'status' => Campaign::STATUS_SENT,
//        ],
        [
            'originId' => 'campaign7',
            'webID' => '117',
            'reference' => 'mailchimp_campaign7',
            'status' => Campaign::STATUS_SCHEDULE,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadSubscribersListData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $entity = new Campaign();

            $entity->setSubscribersList($this->getReference('mailchimp_subscribers_list'));

            $this->setEntityPropertyValues($entity, $data, ['reference']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
