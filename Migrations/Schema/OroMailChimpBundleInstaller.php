<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroMailChimpBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_6';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroIntegrationTransportTable($schema);
        $this->createOrocrmCmpgnTransportStngsTable($schema);
        $this->createOrocrmMailchimpMemberActivityTable($schema);
        $this->createOrocrmMailchimpCampaignTable($schema);
        $this->createOrocrmMailchimpTemplateTable($schema);
        $this->createOrocrmMailchimpMemberTable($schema);
        $this->createOrocrmMailchimpStaticSegmentMemberTable($schema);
        $this->createOrocrmMailchimpStaticSegmentTable($schema);
        $this->createOrocrmMailchimpSubscribersListTable($schema);
        $this->createOrocrmMcExtendedMergeVarTable($schema);
        $this->createOrocrmMcMmbrExtdMergeVarTable($schema);
        $this->createOrocrmMailchimpMlEmailTable($schema);
        $this->createOrocrmMcTmpMmbrToRemoveTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmCmpgnTransportStngsForeignKeys($schema);
        $this->addOrocrmMailchimpMemberActivityForeignKeys($schema);
        $this->addOrocrmMailchimpCampaignForeignKeys($schema);
        $this->addOrocrmMailchimpTemplateForeignKeys($schema);
        $this->addOrocrmMailchimpMemberForeignKeys($schema);
        $this->addOrocrmMailchimpStaticSegmentMemberForeignKeys($schema);
        $this->addOrocrmMailchimpStaticSegmentForeignKeys($schema);
        $this->addOrocrmMailchimpSubscribersListForeignKeys($schema);
        $this->addOrocrmMcExtendedMergeVarForeignKeys($schema);
        $this->addOrocrmMcMmbrExtdMergeVarForeignKeys($schema);
        $this->addOrocrmMcTmpMmbrToRemoveForeignKeys($schema);
        $this->addOrocrmMailchimpMlEmailForeignKeys($schema);
    }

    /**
     * Create oro_integration_transport table
     *
     * @param Schema $schema
     */
    protected function createOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('orocrm_mailchimp_apikey', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('orocrm_mailchimp_act_up_int', 'integer', ['notnull' => false]);
    }

    /**
     * Create orocrm_cmpgn_transport_stngs table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCmpgnTransportStngsTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_cmpgn_transport_stngs');
        $table->addColumn('mailchimp_template_id', 'integer', ['notnull' => false]);
        $table->addColumn('mailchimp_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('mailchimp_receive_activities', 'boolean', ['notnull' => false]);
    }

    /**
     * Create orocrm_mc_mmbr_activity table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMailchimpMemberActivityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mc_mmbr_activity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('member_id', 'integer', ['notnull' => false]);
        $table->addColumn('campaign_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('action', 'string', ['length' => 25]);
        $table->addColumn('ip_address', 'string', ['notnull' => false, 'length' => 45]);
        $table->addColumn('activity_time', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('url', 'text', ['notnull' => false]);
        $table->addIndex(['action'], 'mc_mmbr_activity_action_idx', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_mailchimp_campaign table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMailchimpCampaignTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mailchimp_campaign');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('email_campaign_id', 'integer', ['notnull' => false]);
        $table->addColumn('subscribers_list_id', 'integer', ['notnull' => false]);
        $table->addColumn('static_segment_id', 'integer', ['notnull' => false]);
        $table->addColumn('template_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => true]);
        $table->addColumn('origin_id', 'string', ['length' => 32]);
        $table->addColumn('web_id', 'bigint', []);
        $table->addColumn('title', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('from_email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('from_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('content_type', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('type', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('status', 'string', ['length' => 16]);
        $table->addColumn('send_time', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('last_open_date', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('archive_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('archive_url_long', 'text', ['notnull' => false]);
        $table->addColumn('emails_sent', 'integer', ['notnull' => false]);
        $table->addColumn('tests_sent', 'integer', ['notnull' => false]);
        $table->addColumn('tests_remain', 'integer', ['notnull' => false]);
        $table->addColumn('syntax_errors', 'integer', ['notnull' => false]);
        $table->addColumn('hard_bounces', 'integer', ['notnull' => false]);
        $table->addColumn('soft_bounces', 'integer', ['notnull' => false]);
        $table->addColumn('unsubscribes', 'integer', ['notnull' => false]);
        $table->addColumn('abuse_reports', 'integer', ['notnull' => false]);
        $table->addColumn('forwards', 'integer', ['notnull' => false]);
        $table->addColumn('forwards_opens', 'integer', ['notnull' => false]);
        $table->addColumn('opens', 'integer', ['notnull' => false]);
        $table->addColumn('unique_opens', 'integer', ['notnull' => false]);
        $table->addColumn('clicks', 'integer', ['notnull' => false]);
        $table->addColumn('unique_clicks', 'integer', ['notnull' => false]);
        $table->addColumn('users_who_clicked', 'integer', ['notnull' => false]);
        $table->addColumn('unique_likes', 'integer', ['notnull' => false]);
        $table->addColumn('recipient_likes', 'integer', ['notnull' => false]);
        $table->addColumn('facebook_likes', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['origin_id', 'channel_id'], 'mc_campaign_oid_cid_unq');
        $table->addUniqueIndex(['email_campaign_id'], 'uniq_9472018ce0f98bc3');
    }

    /**
     * Create orocrm_mailchimp_template table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMailchimpTemplateTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mailchimp_template');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => true]);
        $table->addColumn('origin_id', 'bigint', []);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('is_active', 'boolean', []);
        $table->addColumn('layout', 'text', ['notnull' => false]);
        $table->addColumn('category', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('preview_image', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['category'], 'mc_template_category', []);
        $table->addUniqueIndex(['origin_id', 'channel_id'], 'mc_template_oid_cid_unq');
        $table->addIndex(['name'], 'mc_template_name', []);
    }

    /**
     * Create orocrm_mailchimp_member table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMailchimpMemberTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mailchimp_member');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('subscribers_list_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => true]);
        $table->addColumn('origin_id', 'bigint', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('status', 'string', ['length' => 16]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('member_rating', 'smallint', ['notnull' => false]);
        $table->addColumn('optedin_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('optedin_ip', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('confirmed_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('confirmed_ip', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('latitude', 'string', ['notnull' => false, 'length' => 64]);
        $table->addColumn('longitude', 'string', ['notnull' => false, 'length' => 64]);
        $table->addColumn('gmt_offset', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('dst_offset', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('timezone', 'string', ['notnull' => false, 'length' => 40]);
        $table->addColumn('cc', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('region', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_changed_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('euid', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('merge_var_values', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addIndex(['email', 'subscribers_list_id'], 'mc_mmbr_email_list_idx');
        $table->addIndex(['origin_id'], 'mc_mmbr_origin_idx', []);
        $table->addIndex(['status'], 'mc_mmbr_status_idx', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_mc_static_segment_mmbr table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMailchimpStaticSegmentMemberTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mc_static_segment_mmbr');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('member_id', 'integer', []);
        $table->addColumn('static_segment_id', 'integer', []);
        $table->addColumn('state', 'string', ['length' => 255]);
        $table->addUniqueIndex(['static_segment_id', 'member_id'], 'mc_segment_sid_mid_unq');
        $table->addIndex(['static_segment_id', 'state'], 'mc_segment_mmbr_sid_st');
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_mc_static_segment table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMailchimpStaticSegmentTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mc_static_segment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('subscribers_list_id', 'integer', ['notnull' => false]);
        $table->addColumn('marketing_list_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => true]);
        $table->addColumn('name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('origin_id', 'bigint', ['notnull' => false]);
        $table->addColumn('sync_status', 'string', ['length' => 255]);
        $table->addColumn('last_synced', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('remote_remove', 'boolean', []);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('last_reset', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('member_count', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_mc_subscribers_list table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMailchimpSubscribersListTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mc_subscribers_list');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => true]);
        $table->addColumn('origin_id', 'string', ['length' => 32]);
        $table->addColumn('web_id', 'bigint', []);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('email_type_option', 'boolean', []);
        $table->addColumn('use_awesomebar', 'boolean', []);
        $table->addColumn('default_from_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('default_from_email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('default_subject', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('default_language', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('list_rating', 'float', ['notnull' => false]);
        $table->addColumn('subscribe_url_short', 'text', ['notnull' => false]);
        $table->addColumn('subscribe_url_long', 'text', ['notnull' => false]);
        $table->addColumn('beamer_address', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('visibility', 'text', ['notnull' => false]);
        $table->addColumn('member_count', 'float', ['notnull' => false]);
        $table->addColumn('unsubscribe_count', 'float', ['notnull' => false]);
        $table->addColumn('cleaned_count', 'float', ['notnull' => false]);
        $table->addColumn('member_count_since_send', 'float', ['notnull' => false]);
        $table->addColumn('unsubscribe_count_since_send', 'float', ['notnull' => false]);
        $table->addColumn('cleaned_count_since_send', 'float', ['notnull' => false]);
        $table->addColumn('campaign_count', 'float', ['notnull' => false]);
        $table->addColumn('grouping_count', 'float', ['notnull' => false]);
        $table->addColumn('group_count', 'float', ['notnull' => false]);
        $table->addColumn('merge_var_count', 'float', ['notnull' => false]);
        $table->addColumn('avg_sub_rate', 'float', ['notnull' => false]);
        $table->addColumn('avg_unsub_rate', 'float', ['notnull' => false]);
        $table->addColumn('target_sub_rate', 'float', ['notnull' => false]);
        $table->addColumn('open_rate', 'float', ['notnull' => false]);
        $table->addColumn('click_rate', 'float', ['notnull' => false]);
        $table->addColumn('merge_var_config', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_mc_extended_merge_var table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMcExtendedMergeVarTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mc_extended_merge_var');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('static_segment_id', 'integer', []);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('is_required', 'boolean', []);
        $table->addColumn('field_type', 'string', ['length' => 255]);
        $table->addColumn('tag', 'string', ['length' => 10]);
        $table->addColumn('state', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['static_segment_id', 'name'], 'mc_emv_sid_name_unq');
    }

    /**
     * Create orocrm_mc_mmbr_extd_merge_var table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMcMmbrExtdMergeVarTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_mc_mmbr_extd_merge_var');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('member_id', 'integer', []);
        $table->addColumn('static_segment_id', 'integer', []);
        $table->addColumn('merge_var_values', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('state', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['static_segment_id', 'member_id'], 'mc_mmbr_emv_sid_mmbr_unq');
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
        $table->addColumn('state', 'string', ['length' => 25]);
        $table->addIndex(['state'], 'mc_smbr_rm_state_idx', []);
        $table->setPrimaryKey(['member_id', 'static_segment_id']);
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
        $table->addColumn('state', 'string', ['length' => 25]);
        $table->setPrimaryKey(['marketing_list_id', 'email']);
        $table->addIndex(['state'], 'mc_ml_email_state_idx', []);
        $table->addIndex(['email'], 'mc_ml_email_idx', []);
    }

    /**
     * Add orocrm_cmpgn_transport_stngs foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCmpgnTransportStngsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_cmpgn_transport_stngs');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mailchimp_template'),
            ['mailchimp_template_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['mailchimp_channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_mc_mmbr_activity foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMailchimpMemberActivityForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mc_mmbr_activity');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mailchimp_member'),
            ['member_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mailchimp_campaign'),
            ['campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_mailchimp_campaign foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMailchimpCampaignForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mailchimp_campaign');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_campaign_email'),
            ['email_campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mc_subscribers_list'),
            ['subscribers_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mc_static_segment'),
            ['static_segment_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mailchimp_template'),
            ['template_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_mailchimp_template foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMailchimpTemplateForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mailchimp_template');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_mailchimp_member foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMailchimpMemberForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mailchimp_member');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mc_subscribers_list'),
            ['subscribers_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_mc_static_segment_mmbr foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMailchimpStaticSegmentMemberForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mc_static_segment_mmbr');
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
     * Add orocrm_mc_static_segment foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMailchimpStaticSegmentForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mc_static_segment');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mc_subscribers_list'),
            ['subscribers_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list'),
            ['marketing_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_mc_subscribers_list foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMailchimpSubscribersListForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mc_subscribers_list');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_mc_extended_merge_var foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMcExtendedMergeVarForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mc_extended_merge_var');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mc_static_segment'),
            ['static_segment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add orocrm_mc_mmbr_extd_merge_var foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMcMmbrExtdMergeVarForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_mc_mmbr_extd_merge_var');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mailchimp_member'),
            ['member_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_mc_static_segment'),
            ['static_segment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
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
