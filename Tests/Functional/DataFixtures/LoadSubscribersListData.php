<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use Symfony\Component\PropertyAccess\PropertyAccess;

class LoadSubscribersListData extends AbstractFixture implements DependentFixtureInterface
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
            $entity = new SubscribersList();

            $entity->setChannel($this->getReference('mailchimp_transport:test_transport1'));

            $this->setEntityPropertyValues($entity, $data, ['reference']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
