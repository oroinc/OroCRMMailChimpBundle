<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Connector;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

abstract class AbstractMailChimpConnector extends AbstractConnector
{
    /**
     * @var MailChimpTransport
     */
    protected $transport;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function setManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastSyncDate()
    {
        $channel = $this->getChannel();
        $repository = $this->managerRegistry->getRepository('OroIntegrationBundle:Status');

        /**
         * @var Status $status
         */
        $status = $repository->findOneBy(
            ['code' => Status::STATUS_COMPLETED, 'channel' => $channel, 'connector' => $this->getType()],
            ['date' => 'DESC']
        );

        return $status ? $status->getDate() : null;
    }

    /**
     * @return Channel
     */
    protected function getChannel()
    {
        return $this->contextMediator->getChannel($this->getContext());
    }
}
