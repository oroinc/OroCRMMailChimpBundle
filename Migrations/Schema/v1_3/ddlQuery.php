<?php

namespace OroCRM\Bundle\MailChimpBundle\Migrations\Schema\v1_3;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class ddlQuery extends SqlMigrationQuery
{
    /**
     * @param LoggerInterface $logger
     *
     * {inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        foreach ($this->queries as $query) {
            $logger->notice($query);
            $this->connection->exec($query);
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
