<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\MigrationConstraintTrait;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMailChimpBundle implements Migration
{
    use MigrationConstraintTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->clearEntriesWithNullChannel($queries);
        $this->addOnDeleteCascadeForChannelConnections($schema);
        $this->clearOrphanedMarketingListEmailEntries($queries);
    }

    /**
     * @param Schema $schema
     */
    private function addOnDeleteCascadeForChannelConnections(Schema $schema)
    {
        $mailChimpCampaignTable = $schema->getTable('orocrm_mailchimp_campaign');
        $mailChimpCampaignChannelForeignKey =
            $this->getConstraintName($mailChimpCampaignTable, 'channel_id');
        $mailChimpCampaignTable->removeForeignKey($mailChimpCampaignChannelForeignKey);
        $mailChimpCampaignTable->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $mailChimpCampaignTable->getColumn('channel_id')->setNotnull(true);

        $mailChimpTemplateTable = $schema->getTable('orocrm_mailchimp_template');
        $mailChimpTemplateChannelForeignKey =
            $this->getConstraintName($mailChimpTemplateTable, 'channel_id');
        $mailChimpTemplateTable->removeForeignKey($mailChimpTemplateChannelForeignKey);
        $mailChimpTemplateTable->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $mailChimpTemplateTable->getColumn('channel_id')->setNotnull(true);

        $mailChimpMemberTable = $schema->getTable('orocrm_mailchimp_member');
        $mailChimpMemberChannelForeignKey =
            $this->getConstraintName($mailChimpMemberTable, 'channel_id');
        $mailChimpMemberTable->removeForeignKey($mailChimpMemberChannelForeignKey);
        $mailChimpMemberTable->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $mailChimpMemberTable->getColumn('channel_id')->setNotnull(true);

        $mailChimpStaticSegmentTable = $schema->getTable('orocrm_mc_static_segment');
        $mailChimpStaticSegmentChannelForeignKey =
            $this->getConstraintName($mailChimpStaticSegmentTable, 'channel_id');
        $mailChimpStaticSegmentTable->removeForeignKey($mailChimpStaticSegmentChannelForeignKey);
        $mailChimpStaticSegmentTable->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $mailChimpStaticSegmentTable->getColumn('channel_id')->setNotnull(true);

        $mailChimpSubscribersListTable = $schema->getTable('orocrm_mc_subscribers_list');
        $mailChimpSubscribersListChannelForeignKey =
            $this->getConstraintName($mailChimpSubscribersListTable, 'channel_id');
        $mailChimpSubscribersListTable->removeForeignKey($mailChimpSubscribersListChannelForeignKey);
        $mailChimpSubscribersListTable->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $mailChimpSubscribersListTable->getColumn('channel_id')->setNotnull(true);
    }

    /**
     * @param QueryBag $queries
     */
    private function clearEntriesWithNullChannel(QueryBag $queries)
    {
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery('DELETE FROM orocrm_mailchimp_campaign WHERE channel_id is NULL')
        );

        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery('DELETE FROM orocrm_mailchimp_template WHERE channel_id is NULL')
        );

        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery('DELETE FROM orocrm_mailchimp_member WHERE channel_id is NULL')
        );

        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery('DELETE FROM orocrm_mc_static_segment WHERE channel_id is NULL')
        );

        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery('DELETE FROM orocrm_mc_subscribers_list WHERE channel_id is NULL')
        );
    }

    /**
     * @param QueryBag $queries
     */
    private function clearOrphanedMarketingListEmailEntries(QueryBag $queries)
    {
        $queries->addQuery(
            new ParametrizedSqlMigrationQuery(
                'DELETE FROM orocrm_mailchimp_ml_email'
                        . ' WHERE marketing_list_id NOT IN'
                        . ' (SELECT DISTINCT segment.marketing_list_id FROM orocrm_mc_static_segment segment)'
            )
        );
    }
}
