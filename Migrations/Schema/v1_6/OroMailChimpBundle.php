<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\MigrationConstraintTrait;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMailChimpBundle implements Migration
{
    use MigrationConstraintTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->setOnDeleteCascadeForMemberSubscribersListConnection($schema);
    }

    private function setOnDeleteCascadeForMemberSubscribersListConnection(Schema $schema)
    {
        $mailChimpMemberTable = $schema->getTable('orocrm_mailchimp_member');
        $mailChimpMemberSubscribersListForeignKey =
            $this->getConstraintName($mailChimpMemberTable, 'subscribers_list_id');
        $mailChimpMemberTable->removeForeignKey($mailChimpMemberSubscribersListForeignKey);
        $mailChimpMemberTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_mc_subscribers_list'),
            ['subscribers_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
