<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

class MemberWriter extends AbstractExportWriter
{
    /**
     * @var MailChimpTransport
     */
    protected $transport;

    /**
     * @param Member[] $items
     *
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $itemsByLists = [];

        foreach ($items as $item) {
            $itemsByLists[$item->getSubscribersList()->getOriginId()][] = $item;
        }

        foreach ($itemsByLists as $listId => $items) {
            $item = reset($items);
            $this->transport->init($item->getChannel()->getTransport());

            $emails = array_map(
                function (Member $member) {
                    return ['email' => ['email' => $member->getEmail()]];
                },
                $items
            );

            /** @todo: add result logging */
            $this->transport->batchSubscribe(
                [
                    'id' => $listId,
                    'batch' => $emails,
                    'double_optin' => false
                ]
            );
        }
    }
}
