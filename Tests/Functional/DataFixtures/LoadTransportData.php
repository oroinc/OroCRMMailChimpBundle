<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransport;

class LoadTransportData extends AbstractMailChimpFixture implements ContainerAwareInterface
{
    /**
     * @var array Transports configuration
     */
    protected $transportData = [
        [
            'reference' => 'mailchimp:transport_one',
            'apiKey' => 'f9e179585f382c4def28653b1cbddba5-us9',
        ],
        [
            'reference' => 'mailchimp:transport_two',
            'apiKey' => 'f9e179585f382c4def28653b1cbddba5-us9',
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
        foreach ($this->transportData as $data) {
            $entity = new MailChimpTransport();
            $this->setEntityPropertyValues($entity, $data, ['reference']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
