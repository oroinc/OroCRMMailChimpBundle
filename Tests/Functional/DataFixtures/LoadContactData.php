<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;

class LoadContactData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $contactsData = [
        [
            'firstName' => 'Daniel',
            'lastName'  => 'Case',
            'email'     => 'member1@example.com',
        ],
        [
            'firstName' => 'John',
            'lastName'  => 'Case',
            'email'     => 'member2@example.com',
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->contactsData as $contactData) {
            $contact = new Contact();
            $contact->setOwner($user);
            $contact->setOrganization($organization);
            $contact->setFirstName($contactData['firstName']);
            $contact->setLastName($contactData['lastName']);
            $email = new ContactEmail();
            $email->setEmail($contactData['email']);
            $email->setPrimary(true);
            $contact->addEmail($email);

            $manager->persist($contact);
            $this->setReference('contact:' . $contactData['email'], $contact);
        }

        $manager->flush();
    }
}
