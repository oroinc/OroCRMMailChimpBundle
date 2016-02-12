<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Connector;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

abstract class AbstractMailChimpConnector extends AbstractConnector
{
    const LAST_SYNC_DATE_KEY = 'lastSyncDate';

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
        if ((bool)$this->getContext()->getOption('force')) {
            return null;
        }

        $channel = $this->getChannel();
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository('OroIntegrationBundle:Status');

        /**
         * @var Status $status
         */
        $status = $repository->findOneBy(
            ['code' => Status::STATUS_COMPLETED, 'channel' => $channel, 'connector' => $this->getType()],
            ['date' => 'DESC']
        );

        $timezone = new \DateTimeZone('UTC');
        $date = new \DateTime('now', $timezone);
        $context = $this->getStepExecution()->getExecutionContext();
        $data = $context->get(ConnectorInterface::CONTEXT_CONNECTOR_DATA_KEY) ?: [];
        $context->put(
            ConnectorInterface::CONTEXT_CONNECTOR_DATA_KEY,
            array_merge($data, [self::LAST_SYNC_DATE_KEY => $date->format(\DateTime::ISO8601)])
        );

        if (!$status) {
            return null;
        }

        $data = $status->getData();

        if (empty($data)) {
            return null;
        }

        if (!empty($data[self::LAST_SYNC_DATE_KEY])) {
            return new \DateTime($data[self::LAST_SYNC_DATE_KEY], $timezone);
        }

        return null;
    }

    /**
     * @return Channel
     */
    protected function getChannel()
    {
        return $this->contextMediator->getChannel($this->getContext());
    }
}
