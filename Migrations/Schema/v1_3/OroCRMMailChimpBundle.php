<?php

namespace OroCRM\Bundle\MailChimpBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMMailChimpBundle implements Migration, DatabasePlatformAwareInterface
{
    /** @var AbstractPlatform; */
    protected $platform;

    /**
     * {@inheritdoc}
     */
    public function setDatabasePlatform(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_mailchimp_ml_email');

        if ($this->platform instanceof PostgreSqlPlatform) {
            $ddlQuery = new DdlQuery();
            $sql = 'ALTER TABLE orocrm_mailchimp_ml_email DROP CONSTRAINT ' . $table->getPrimaryKey()->getName();
            $ddlQuery->addSql($sql);
            $sql = 'ALTER TABLE orocrm_mailchimp_ml_email DROP COLUMN id';
            $ddlQuery->addSql($sql);
            $sql = 'ALTER TABLE orocrm_mailchimp_ml_email ADD CONSTRAINT ' . $table->getPrimaryKey()->getName() .
                ' PRIMARY KEY(marketing_list_id, email)';
            $ddlQuery->addSql($sql);
            $queries->addQuery($ddlQuery);
        } else {
            $table->dropPrimaryKey();
            $table->dropColumn('id');
            $table->setPrimaryKey(['marketing_list_id', 'email']);
        }
    }
}
