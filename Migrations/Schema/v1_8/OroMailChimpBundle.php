<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\MigrationConstraintTrait;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMailChimpBundle implements Migration, DatabasePlatformAwareInterface
{
    use MigrationConstraintTrait;

    /**
     * @var AbstractPlatform $platform
     */
    private $platform;

    /**
     * Sets the database platform
     *
     * @param AbstractPlatform $platform
     */
    public function setDatabasePlatform(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }


    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->changeOriginIdOnMailchimpMember($schema);
        $queries->addPostQuery(new UpdateOriginIdQuery($this->platform));
    }

    /**
     * @param Schema $schema
     * @throws SchemaException
     * @throws DBALException
     */
    private function changeOriginIdOnMailchimpMember(Schema $schema)
    {
        $mailChimpMemberTable = $schema->getTable('orocrm_mailchimp_member');

        $mailChimpMemberTable
            ->addColumn('leid', Type::BIGINT)
            ->setNotnull(false);

        $mailChimpMemberTable
            ->getColumn('origin_id')
            ->setType(Type::getType(Type::STRING))
            ->setNotnull(false)
            ->setLength(32);
    }
}
