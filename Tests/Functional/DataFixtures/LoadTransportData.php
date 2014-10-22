<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransport;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class LoadTransportData extends AbstractFixture implements ContainerAwareInterface
{
    protected $transportData = array(
        array(
            'reference' => 'mailchimp_transport:test_transport1',
            'apiKey' => 'f9e179585f382c4def28653b1cbddba5-us9',
        ),
        array(
            'reference' => 'mailchimp_transport:test_transport2',
            'apiKey' => 'f9e179585f382c4def28653b1cbddba5-us9',
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

    public function a($i)
    {
        return $i * 2;
    }

    public function b($i)
    {
        return $i * 2;
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
        foreach ($this->transportData as $data) {
            $entity = new MailChimpTransport();
            $this->setEntityPropertyValues($entity, $data, array('reference'));
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
