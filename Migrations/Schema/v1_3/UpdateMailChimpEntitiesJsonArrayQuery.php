<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Platforms\PostgreSQL92Platform;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateMailChimpEntitiesJsonArrayQuery extends ParametrizedMigrationQuery
{
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info(
            'Convert columns with "json_array(text)" type to "json_array" type on PostgreSQL >= 9.2 and Doctrine 2.5'
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
        $tables = [
            // table                        => column
            'orocrm_mailchimp_member'       => 'merge_var_values',
            'orocrm_mc_mmbr_extd_merge_var' => 'merge_var_values',
            'orocrm_mc_subscribers_list'    => 'merge_var_config',
        ];
        $platform = $this->connection->getDatabasePlatform();
        if ($platform instanceof PostgreSQL92Platform) {
            foreach ($tables as $table => $column) {
                $updateSql = 'ALTER TABLE ' . $table . ' ALTER COLUMN ' . $column .
                    ' TYPE JSON USING ' . $column . '::JSON';

                $this->logQuery($logger, $updateSql);
                if (!$dryRun) {
                    $this->connection->executeUpdate($updateSql);
                }
            }
        }
    }
}
