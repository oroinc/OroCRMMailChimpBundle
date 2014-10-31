<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;

class MemberWriter extends AbstractExportWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var Member $item */
        $item = reset($items);
        $this->transport->init($item->getChannel()->getTransport());

        $emails = array_map(
            function (Member $member) {
                return ['email' => ['email' => $member->getEmail()]];
            },
            $items
        );

        $subscribersList = $item->getSubscribersList();
        $originId = $subscribersList->getOriginId();

        $response = $this->transport->batchSubscribe(
            [
                'id' => $originId,
                'batch' => $emails,
                'double_optin' => false
            ]
        );

        if ($this->logger && is_array($response)) {
            $this->logger->info(
                sprintf(
                    'List #%s [origin_id=%s]: [%s] add, [%s] update, [%s] error',
                    $subscribersList->getId(),
                    $originId,
                    $response['add_count'],
                    $response['update_count'],
                    $response['error_count']
                )
            );

            if ($response['errors']) {
                foreach ($response['errors'] as $error) {
                    $this->logger->warning(
                        sprintf('[Error #%s] %s', $error['code'], $error['error'])
                    );
                }
            }
        }
    }
}
