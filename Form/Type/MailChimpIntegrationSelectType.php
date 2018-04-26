<?php

namespace Oro\Bundle\MailChimpBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\MailChimpBundle\Provider\ChannelType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailChimpIntegrationSelectType extends AbstractType
{
    const NAME = 'oro_mailchimp_integration_select';
    const ENTITY = 'Oro\Bundle\IntegrationBundle\Entity\Channel';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param ManagerRegistry $registry
     * @param AclHelper $aclHelper
     */
    public function __construct(ManagerRegistry $registry, AclHelper $aclHelper)
    {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $integrations = $this->getMailChimpIntegrations();
        $options = [
            'class' => self::ENTITY,
            'choice_label' => 'name',
            'choices' => $integrations
        ];

        if (count($integrations) != 1) {
            $options['placeholder'] = 'oro.mailchimp.emailcampaign.integration.placeholder';
        }
        $resolver->setDefaults($options);
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
            ->andWhere('c.type = :mailChimpType')
            ->andWhere('c.enabled = :enabled')
            ->setParameter('enabled', true)
            ->setParameter('mailChimpType', ChannelType::TYPE)
            ->orderBy('c.name', 'ASC');
        $query = $this->aclHelper->apply($qb);

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
