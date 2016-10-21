<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Types\Type;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateActiveCampaignsQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->updateSettings($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->updateSettings($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     */
    protected function updateSettings(LoggerInterface $logger, $dryRun = false)
    {
        $update = 'UPDATE orocrm_cmpgn_transport_stngs
          SET mailchimp_receive_activities = :receiveActivities
          WHERE type = :type';
        $params = ['receiveActivities' => true, 'type' => 'mailchimptransportsettings'];
        $types = ['receiveActivities' => Type::BOOLEAN, 'type' => Type::STRING];
        $this->logQuery($logger, $update, $params, $types);

        if (!$dryRun) {
            $this->connection->executeUpdate($update, $params, $types);
        }
    }
}
