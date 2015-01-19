<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\UserBundle\Entity\User;

class LoadSegmentData extends AbstractMailChimpFixture
{
    /**
     * @var array Channels configuration
     */
    protected $data = [
        [
            'type' => 'dynamic',
            'name' => 'Test ML Segment',
            'description' => 'description',
            'entity' => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
            'definition' => [
                'columns' =>
                    [
                        [
                            'name' => 'primaryEmail',
                            'label' => 'Primary Email',
                            'sorting' => '',
                            'func' => null,
                        ],
                    ],
                'filters' =>
                    [
                        [
                            'columnName' => 'firstName',
                            'criterion' =>
                                [
                                    'filter' => 'string',
                                    'data' =>
                                        [
                                            'value' => 'John',
                                            'type' => '2',
                                        ],
                                ],
                        ],
                    ],
            ],
            'reference' => 'mailchimp:ml_one:segment',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->data as $data) {
            $entity = new Segment();
            $type = $manager
                ->getRepository('OroSegmentBundle:SegmentType')
                ->find($data['type']);
            $entity->setType($type);
            $entity->setDefinition(json_encode($data['definition']));
            $entity->setOrganization($organization);
            $entity->setOwner($user->getBusinessUnits()->first());

            $this->setEntityPropertyValues($entity, $data, ['reference', 'type', 'definition']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
