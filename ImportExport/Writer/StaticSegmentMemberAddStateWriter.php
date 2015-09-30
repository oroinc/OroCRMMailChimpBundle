<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

class StaticSegmentMemberAddStateWriter extends AbstractInsertFromSelectWriter
{
    /**
     * {@inheritdoc}
     */
    protected function getInsert()
    {
        return 'INSERT INTO orocrm_mc_static_segment_mmbr(member_id, static_segment_id, state)';
    }
}
