<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class MailChimpIntegrationSelectType extends AbstractType
{
    const NAME = 'orocrm_mailchimp_integration_select';
    const ENTITY = 'Oro\Bundle\IntegrationBundle\Entity\Channel';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    public function __construct(ManagerRegistry $registry, AclHelper $aclHelper)
    {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => self::ENTITY,
                'choices' => $this->getMailChimpIntegrations()
            ]
        );
    }

    /**
     * Get integration with type mailchimp.
     *
     * @return array
     */
    protected function getMailChimpIntegrations()
    {
        $qb = $this->registry->getRepository(self::ENTITY)
            ->createQueryBuilder('c')
            ->select(['c.id', 'c.name'])
            ->andWhere('c.type = :mailChimpType')
            ->setParameter('mailChimpType', 'mailchimp')
            ->orderBy('c.name', 'ASC');
        $query = $this->aclHelper->apply($qb);

        $channels = $query->getArrayResult();
        $result = [];
        foreach ($channels as $channel) {
            $result[$channel['id']] = $channel['name'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
