<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use Symfony\Component\DependencyInjection\ContainerInterface;

//class LoadMemberData extends AbstractMailChimpFixture implements DependentFixtureInterface
//{
//    /**
//     * @var array
//     */
//    protected $data = [
//        [
//            'originId' => 210000000,
//            'reference' => 'mailchimp_member',
//            'email' => 'member1@example.com',
//            'status' => Member::STATUS_SUBSCRIBED,
//        ],
//        [
//            'originId' => 210000001,
//            'reference' => 'mailchimp_member',
//            'email' => 'member2@example.com',
//            'status' => Member::STATUS_SUBSCRIBED,
//        ]
//    ];

class LoadMemberData extends AbstractMailChimpFixture implements DependentFixtureInterface
{
    /**
     * @var array Channels configuration
     */
    protected $data = [
        [
            'originId' => 210000000,
            'email' => 'member1@example.com',
            'status' => Member::STATUS_SUBSCRIBED,
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'channel' => 'mailchimp:channel_1',
            'reference' => 'mailchimp:member_one',
        ],
        [
            'originId' => 210000001,
            'email' => 'member2@example.com',
            'status' => Member::STATUS_SUBSCRIBED,
            'subscribersList' => 'mailchimp:subscribers_list_one',
            'channel' => 'mailchimp:channel_1',
            'reference' => 'mailchimp:member_one',
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
            $entity = new Member();
            $data['subscribersList'] = $this->getReference($data['subscribersList']);
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
            'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadCampaignData',
        );
    }
}
