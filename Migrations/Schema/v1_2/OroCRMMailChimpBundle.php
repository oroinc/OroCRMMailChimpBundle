<?php

namespace OroCRM\Bundle\MailChimpBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMMailChimpBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createOrocrmMailchimpMlEmailTable($schema);
        $this->addOrocrmMailchimpMlEmailForeignKeys($schema);
    }

    /**
     * Create orocrm_mailchimp_ml_email table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMailchimpMlEmailTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mailchimp_ml_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('marketing_list_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['marketing_list_id'], 'idx_35d56e8896434d04', []);
        $table->addIndex(['email'], 'mc_ml_email_idx', []);
    }

    /**
     * Add orocrm_mailchimp_ml_email foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMailchimpMlEmailForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mailchimp_ml_email');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list'),
            ['marketing_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
