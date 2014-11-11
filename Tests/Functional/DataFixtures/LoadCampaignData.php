<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;

class LoadCampaignData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $data = [
        [
            'originId' => 'campaign1',
            'webID' => '123',
            'reference' => 'mailchimp_campaign',
            'status' => Campaign::STATUS_SENT,
        ]
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
            $entity = new Campaign();

            $entity->setSubscribersList($this->getReference('mailchimp_subscribers_list'));

            $this->setEntityPropertyValues($entity, $data, ['reference']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
