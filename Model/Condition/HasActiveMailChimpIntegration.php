<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition;
use OroCRM\Bundle\MailChimpBundle\Provider\ChannelType;

/**
 * Check For Active MailChimp integration
 * Usage:
 * @has_active_mailchimp_integration: ~
 */
class HasActiveMailChimpIntegration extends AbstractCondition
{
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
    protected function isConditionAllowed($context)
    {
        return (bool)$this->getActiveMailChimpIntegration();
    }

    /**
     * @return array
     */
    protected function getActiveMailChimpIntegration()
    {
        $qb = $this->registry->getRepository('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->createQueryBuilder('c')
            ->andWhere('c.type = :mailChimpType')
            ->andWhere('c.enabled = :enabled')
            ->setParameter('enabled', true)
            ->setParameter('mailChimpType', ChannelType::TYPE)
            ->setMaxResults(1);
        $query = $this->aclHelper->apply($qb);

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        return $this;
    }
}
