<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Oro\Bundle\EntityBundle\ORM\DatabasePlatformInterface;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateOriginIdQuery extends ParametrizedMigrationQuery
{
    /**
     * @var AbstractPlatform
     */
    private $platform;

    /**
     * @param AbstractPlatform $platform
     */
    public function __construct(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return string|string[]
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info('Update the origin_id to an md5 of the lowercase of the email.');
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
        $updateSqls = [];
        if ($this->platform->getName() === DatabasePlatformInterface::DATABASE_POSTGRESQL) {
            $updateSqls[] = 'UPDATE orocrm_mailchimp_member SET leid = CAST(origin_id as BIGINT)';
        } else {
            $updateSqls[] = 'UPDATE orocrm_mailchimp_member SET leid = origin_id';
        }

        $updateSqls[] = 'UPDATE orocrm_mailchimp_member SET origin_id = md5(lower(email))';

        foreach ($updateSqls as $updateSql) {
            $this->logQuery($logger, $updateSql);
            if (!$dryRun) {
                $this->connection->executeUpdate($updateSql);
            }
        }
    }
}
