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
        $table = $schema->getTable('orocrm_mc_static_segment_mmbr');
        $table->addIndex(['static_segment_id', 'state'], 'mc_segment_mmbr_sid_st');
    }
}
