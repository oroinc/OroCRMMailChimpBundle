<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class LoadChannelData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    protected $channelData = array(
        array(
            'name' => 'mailchimp1',
            'type' => 'mailchimp',
            'transport' => 'mailchimp_transport:test_transport1',
            'connectors' => ['list', 'template', 'campaign', 'member'],
            'enabled' => true,
            'reference' => 'mailchimp_transport:test_transport1',
            'synchronizationSettings' => [
                'isTwoWaySyncEnabled' => true
            ],
        ),
        array(
            'name' => 'mailchimp2',
            'type' => 'mailchimp',
            'transport' => 'mailchimp_transport:test_transport2',
            'connectors' => ['list'],
            'enabled' => true,
            'reference' => 'mailchimp_transport:test_transport2',
            'synchronizationSettings' => [
                'isTwoWaySyncEnabled' => true
            ],
        )
    );

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets $entity object properties from $data array
     *
     * @param object $entity
     * @param array $data
     * @param array $excludeProperties
     */
    public function setEntityPropertyValues($entity, array $data, array $excludeProperties = array())
    {
        foreach ($data as $property => $value) {
            if (in_array($property, $excludeProperties)) {
                continue;
            }
            PropertyAccess::createPropertyAccessor()->setValue($entity, $property, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('oro_user.manager');
        $admin = $userManager->findUserByEmail(LoadAdminUserData::DEFAULT_ADMIN_EMAIL);
        foreach ($this->channelData as $data) {
            $entity = new Channel();
            $data['transport'] = $this->getReference($data['transport']);
            $entity->setDefaultUserOwner($admin);
            $this->setEntityPropertyValues($entity, $data, array('reference', 'synchronizationSettings'));
            $this->setReference($data['reference'], $entity);
            if (isset($data['synchronizationSettings'])) {
                foreach ($data['synchronizationSettings'] as $key => $value) {
                    $entity->getSynchronizationSettingsReference()->offsetSet($key, $value);
                }
            }
            $manager->persist($entity);
        }
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return array(
            'OroCRM\\Bundle\\MailChimpBundle\\Tests\\Functional\\DataFixtures\\LoadTransportData'
        );
    }
}
