<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;

class LoadMemberData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $data = [
        [
            'originId' => 'member1',
            'reference' => 'mailchimp_member',
            'email' => 'member1@example.com',
            'status' => Member::STATUS_SUBSCRIBED,
        ],
        [
            'originId' => 'member2',
            'reference' => 'mailchimp_member',
            'email' => 'member2@example.com',
            'status' => Member::STATUS_SUBSCRIBED,
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadChannelData',
            'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadSubscribersListData',
        ];
    }

    /**
     * @param object $entity
     * @param array $data
     * @param array $excludeProperties
     */
    public function setEntityPropertyValues($entity, array $data, array $excludeProperties = [])
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($data as $property => $value) {
            if (in_array($property, $excludeProperties)) {
                continue;
            }
            $propertyAccessor->setValue($entity, $property, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $entity = new Member();

            $entity->setChannel($this->getReference('mailchimp_transport:test_transport1'));
            $entity->setSubscribersList($this->getReference('mailchimp_subscribers_list'));

            $this->setEntityPropertyValues($entity, $data, ['reference']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
