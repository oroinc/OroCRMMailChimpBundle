<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

class LoadSubscribersListData extends AbstractMailChimpFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $data = [
        [
            'name' => 'list1',
            'originId' => 'list1',
            'webId' => '123',
            'reference' => 'mailchimp_subscribers_list',
            'emailTypeOption' => true,
            'useAwesomebar' => true,
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadChannelData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $entity = new SubscribersList();

            $entity->setChannel($this->getReference('mailchimp_transport:channel1'));

            $this->setEntityPropertyValues($entity, $data, ['reference']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
