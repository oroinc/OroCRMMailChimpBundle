<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

class LoadSubscribersListData extends AbstractMailChimpFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    /**
     * @var array Subscriber list configuration
     */
    protected $data = [
        [
            'channel' => 'mailchimp:channel_1',
            'originId' => '54321',
            'webId' => '12345',
            'name' => 'MC',
            'email_type_option' => '0',
            'merge_var_config' => [
                [
                    "name" => "Email Address",
                    "req" => true,
                    "field_type" => "email",
                    "public" => true,
                    "show" => true,
                    "order" => "1",
                    "default" => null,
                    "helptext" => null,
                    "size" => "25",
                    "tag" => "EMAIL",
                    "id" => 0
                ],
                [
                    "name" => "First Name",
                    "req" => false,
                    "field_type" => "text",
                    "public" => true,
                    "show" => true,
                    "order" => "2",
                    "default" => "",
                    "helptext" => "",
                    "size" => "25",
                    "tag" => "FNAME",
                    "id" => 1
                ],
                [
                    "name" => "Last Name",
                    "req" => false,
                    "field_type" => "text",
                    "public" => true,
                    "show" => true,
                    "order" => "3",
                    "default" => "",
                    "helptext" => "",
                    "size" => "25",
                    "tag" => "LNAME",
                    "id" => 2
                ]
            ],
            'emailTypeOption' => true,
            'useAwesomebar' => true,
            'reference' => 'mailchimp:subscribers_list_one',
        ],
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
        foreach ($this->data as $data) {
            $entity = new SubscribersList();
            $data['channel'] = $this->getReference($data['channel']);
            $this->setEntityPropertyValues($entity, $data, ['reference']);
            $this->setReference($data['reference'], $entity);
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
            'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadChannelData',
        );
    }
}