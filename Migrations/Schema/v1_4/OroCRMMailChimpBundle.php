<?php

namespace OroCRM\Bundle\MailChimpBundle\Migrations\Schema\v1_4;

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
        $table = $schema->getTable('orocrm_mc_static_segment_mmbr');
        $table->addIndex(['static_segment_id', 'state'], 'mc_segment_mmbr_sid_st');

        $this->createOrocrmMailchimpMlEmailTable($schema);
        $this->createOrocrmMcTmpMmbrToRemoveTable($schema);

        $this->addOrocrmMcTmpMmbrToRemoveForeignKeys($schema);
        $this->addOrocrmMailchimpMlEmailForeignKeys($schema);
    }

    /**
     * Create orocrm_mc_tmp_mmbr_to_remove table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMcTmpMmbrToRemoveTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mc_tmp_mmbr_to_remove');
        $table->addColumn('member_id', 'integer', []);
        $table->addColumn('static_segment_id', 'integer', []);
        $table->setPrimaryKey(['member_id']);
    }

    /**
     * Create orocrm_mailchimp_ml_email table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMailchimpMlEmailTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mailchimp_ml_email');
        $table->addColumn('marketing_list_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->setPrimaryKey(['marketing_list_id', 'email']);
        $table->addIndex(['marketing_list_id'], 'idx_35d56e8896434d04', []);
        $table->addIndex(['email'], 'mc_ml_email_idx', []);
    }

    /**
     * Add orocrm_mc_tmp_mmbr_to_remove foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMcTmpMmbrToRemoveForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mc_tmp_mmbr_to_remove');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mailchimp_member'),
            ['member_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mc_static_segment'),
            ['static_segment_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
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
