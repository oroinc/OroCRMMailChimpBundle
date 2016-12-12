<?php

namespace Oro\Bundle\MailChimpBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\NoteBundle\Migration\UpdateNoteAssociationKindForRenamedEntitiesMigration;

class MigrateNotesRelations extends UpdateNoteAssociationKindForRenamedEntitiesMigration
{
    protected $entitiesNames = [
        'Campaign',
        'ExtendedMergeVar',
        'Member',
        'MemberActivity',
        'MemberExtendedMergeVar',
        'StaticSegment',
        'StaticSegmentMember',
        'SubscribersList',
        'Template',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getRenamedEntitiesNames(Schema $schema)
    {
        $oldNameSpace = 'OroCRM\Bundle\MailChimpBundle\Entity';
        $newNameSpace = 'Oro\Bundle\MailChimpBundle\Entity';

        $renamedEntityNamesMapping = [];
        foreach ($this->entitiesNames as $entityName) {
            $renamedEntityNamesMapping["$newNameSpace\\$entityName"] = "$oldNameSpace\\$entityName";
        }

        return $renamedEntityNamesMapping;
    }
}
