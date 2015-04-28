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
        $table = $schema->getTable('orocrm_mailchimp_member');
        $table->addIndex(['email', 'subscribers_list_id'], 'mc_mmbr_email_list_idx');
    }
}
