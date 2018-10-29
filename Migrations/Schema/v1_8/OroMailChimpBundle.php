<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMailChimpBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');
        if (!$table->hasColumn('orocrm_mailchimp_apikey')) {
            $table->addColumn('orocrm_mailchimp_apikey', 'string', ['notnull' => false, 'length' => 255]);
        }

        if (!$table->hasColumn('orocrm_mailchimp_act_up_int')) {
            $table->addColumn('orocrm_mailchimp_act_up_int', 'integer', ['notnull' => false]);
        }

        $table = $schema->getTable('orocrm_cmpgn_transport_stngs');
        if (!$table->hasColumn('mailchimp_template_id')) {
            $table->addColumn('mailchimp_template_id', 'integer', ['notnull' => false]);
            $table->addForeignKeyConstraint(
                $schema->getTable('orocrm_mailchimp_template'),
                ['mailchimp_template_id'],
                ['id'],
                ['onUpdate' => null, 'onDelete' => 'SET NULL']
            );
        }
        if (!$table->hasColumn('mailchimp_channel_id')) {
            $table->addColumn('mailchimp_channel_id', 'integer', ['notnull' => false]);
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_integration_channel'),
                ['mailchimp_channel_id'],
                ['id'],
                ['onUpdate' => null, 'onDelete' => 'SET NULL']
            );
        }
        if (!$table->hasColumn('mailchimp_receive_activities')) {
            $table->addColumn('mailchimp_receive_activities', 'boolean', ['notnull' => false]);
        }
    }
}
