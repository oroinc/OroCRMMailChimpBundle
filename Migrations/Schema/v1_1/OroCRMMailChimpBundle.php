<?php

namespace OroCRM\Bundle\MailChimpBundle\Migrations\Schema\v1_1;

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
        $table = $schema->createTable('orocrm_mc_extended_merge_var');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('static_segment_id', 'integer', []);
        $table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('label', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('is_require', 'boolean', ['default' => '0']);
        $table->addColumn('field_type', 'string', ['length' => 255, 'default' => 'text', 'notnull' => true]);
        $table->addColumn('tag', 'string', ['length' => 10, 'notnull' => true]);
        $table->addColumn('state', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['static_segment_id'], 'IDX_DDE321ACF8DF7CF6', []);
        $table->addUniqueIndex(['static_segment_id', 'name'], 'mc_extended_merge_var_sid_name_unq');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mc_static_segment'),
            ['static_segment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table = $schema->createTable('orocrm_mc_mmbr_extd_merge_var');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('member_id', 'integer', []);
        $table->addColumn('static_segment_id', 'integer', []);
        $table->addColumn('merge_var_values', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('state', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['static_segment_id', 'member_id'], 'mc_mmbr_extd_merge_var_sid_mmbr_unq');
        $table->addIndex(['static_segment_id'], 'IDX_507F296DF8DF7CF6', []);
        $table->addIndex(['member_id'], 'IDX_507F296D7597D3FE', []);
    }
}
