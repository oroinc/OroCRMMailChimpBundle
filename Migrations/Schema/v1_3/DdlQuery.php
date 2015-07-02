<?php

namespace OroCRM\Bundle\MailChimpBundle\Migrations\Schema\v1_3;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class DdlQuery extends SqlMigrationQuery
{
    /**
     * @param LoggerInterface $logger
     *
     * {inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        foreach ($this->queries as $query) {
            $logger->notice($query);
            if (!$dryRun) {
                $this->connection->exec($query);
            }
        }
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
