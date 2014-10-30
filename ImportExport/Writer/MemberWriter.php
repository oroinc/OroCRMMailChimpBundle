<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Psr\Log\LoggerInterface;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

class MemberWriter extends AbstractExportWriter
{
    /**
     * @var MailChimpTransport
     */
    protected $transport;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

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
