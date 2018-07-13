<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Platforms\MySQL57Platform;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

/**
 * Update field database type for json fields on mysql 5.7 to use native JSON
 */
class UpdateJsonArrayQuery extends ParametrizedMigrationQuery
{
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info(
            'Convert a column with "DC2Type:json_array" type to "JSON" type on MySQL >= 5.7.8 and Doctrine 2.7'
        );
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $platform = $this->connection->getDatabasePlatform();
        if ($platform instanceof MySQL57Platform) {
            $updateSqls = [
                "ALTER TABLE orocrm_mailchimp_member " .
                "CHANGE merge_var_values merge_var_values JSON DEFAULT NULL COMMENT '(DC2Type:json_array)'",
                "ALTER TABLE orocrm_mc_mmbr_extd_merge_var " .
                "CHANGE merge_var_values merge_var_values JSON DEFAULT NULL COMMENT '(DC2Type:json_array)'",
                "ALTER TABLE orocrm_mc_subscribers_list " .
                "CHANGE merge_var_config merge_var_config JSON DEFAULT NULL COMMENT '(DC2Type:json_array)'",
            ];

            foreach ($updateSqls as $updateSql) {
                $this->logQuery($logger, $updateSql);
                if (!$dryRun) {
                    $this->connection->executeUpdate($updateSql);
                }
            }
        }
    }
}
