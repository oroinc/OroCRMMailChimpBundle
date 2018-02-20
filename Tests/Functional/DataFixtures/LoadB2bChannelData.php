<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Migrations\Data\ORM\DefaultChannelData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadB2bChannelData extends AbstractFixture implements ContainerAwareInterface
{
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
        $channel = $this->container->get('oro_channel.builder.factory')
            ->createBuilder()
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setEntities([B2bCustomer::class])
            ->setChannelType(DefaultChannelData::B2B_CHANNEL_TYPE)
            ->setName('Test Sales channel')
            ->getChannel();

        $manager->persist($channel);
        $manager->flush();

        $manager->flush();
    }
}
