<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SegmentBundle\Entity\Segment;

class LoadSegmentData extends AbstractMailChimpFixture
{
    /**
     * @var array Channels configuration
     */
    protected $mlData = [
        [
            'type' => 'dynamic',
            'name' => 'Test ML Segment',
            'description' => '',
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
                        [
                            'name' => 'firstName',
                            'label' => 'First name',
                            'sorting' => '',
                            'func' => null,
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
        foreach ($this->mlData as $data) {
            $data['definition'] = json_encode($data['definition']);
            $entity = new Segment();

            $type = $manager
                ->getRepository('OroSegmentBundle:SegmentType')
                ->find($data['type']);
            $entity->setType($type);

            $this->setEntityPropertyValues($entity, $data, ['reference', 'type']);
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
