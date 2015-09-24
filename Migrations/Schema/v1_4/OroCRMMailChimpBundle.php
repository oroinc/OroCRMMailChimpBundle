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
        $this->createOrocrmMcTmpMmbrToRemoveTable($schema);
        $this->addOrocrmMcTmpMmbrToRemoveForeignKeys($schema);
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
}
