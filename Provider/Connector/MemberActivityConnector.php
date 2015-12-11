<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Connector;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\MemberActivityRepository;

class MemberActivityConnector extends AbstractMailChimpConnector implements ConnectorInterface
{
    const TYPE = 'member_activity';
    const JOB_IMPORT = 'mailchimp_member_activity_import';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.mailchimp.connector.member_activity.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return $this->entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::JOB_IMPORT;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        /** @var MemberActivityRepository $repository */
        $repository = $this->managerRegistry->getManagerForClass($this->entityName)
            ->getRepository($this->entityName);

        $latestActivityTimeMap = $repository->getLastSyncedActivitiesByCampaign(
            $this->getChannel(),
            [MemberActivity::ACTIVITY_CLICK, MemberActivity::ACTIVITY_OPEN]
        );

        return $this->transport->getMemberActivitiesToSync($this->getChannel(), $latestActivityTimeMap);
    }
}
