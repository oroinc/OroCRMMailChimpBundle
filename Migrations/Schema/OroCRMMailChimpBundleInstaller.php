<?php

namespace OroCRM\Bundle\MailChimpBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use OroCRM\Bundle\MailChimpBundle\Migrations\Schema\v1_0\OroCRMMailChimpBundle as OroCRMMailChimpBundle10;

class OroCRMMailChimpBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $migration = new OroCRMMailChimpBundle10();
        $migration->up($schema, $queries);
    }
}
