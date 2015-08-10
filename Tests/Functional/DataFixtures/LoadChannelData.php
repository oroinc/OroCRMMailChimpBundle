<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;

class LoadChannelData extends AbstractMailChimpFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var array Channels configuration
     */
    protected $channelData = [
        [
            'name' => 'mailchimp1',
            'type' => 'mailchimp',
            'transport' => 'mailchimp:transport_one',
            'connectors' => ['list', 'campaign', 'static_segment', 'member', 'member_activity'],
            'enabled' => true,
            'reference' => 'mailchimp:channel_1',
            'synchronizationSettings' => [
                'isTwoWaySyncEnabled' => true
            ],
        ],
        [
            'name' => 'mailchimp2',
            'type' => 'mailchimp',
            'transport' => 'mailchimp:transport_two',
            'connectors' => ['list'],
            'enabled' => true,
            'reference' => 'mailchimp_transport:channel_2',
            'synchronizationSettings' => [
                'isTwoWaySyncEnabled' => true
            ],
        ]
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

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
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->channelData as $data) {
            $entity = new Channel();
            $data['transport'] = $this->getReference($data['transport']);
            $entity->setDefaultUserOwner($admin);
            $entity->setOrganization($organization);
            $this->setEntityPropertyValues($entity, $data, ['reference', 'synchronizationSettings']);
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
        return [
            __NAMESPACE__ . '\LoadTransportData'
        ];
    }
}
