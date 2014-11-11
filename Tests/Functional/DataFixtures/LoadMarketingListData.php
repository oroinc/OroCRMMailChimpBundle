<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class LoadMarketingListData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var array Channels configuration
     */
    protected $mlData = [
        [
            'type' => 'dynamic',
            'name' => 'Test ML',
            'description' => '',
            'entity' => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
            'reference' => 'mailchimp:ml_one',
        ],
    ];

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
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->mlData as $data) {
            $entity = new MarketingList();
            $type = $manager
                ->getRepository('OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType')
                ->find($data['type']);
            $entity->setType($type);
            $this->setEntityPropertyValues($entity, $data, ['reference', 'type']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
