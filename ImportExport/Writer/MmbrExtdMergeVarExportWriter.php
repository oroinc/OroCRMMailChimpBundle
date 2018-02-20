<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use Oro\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar;
use Psr\Log\LoggerInterface;
use ZfrMailChimp\Exception\Ls\NotSubscribedException;

class MmbrExtdMergeVarExportWriter extends AbstractExportWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var ExtendedMergeVar $item */
        $item = $items[0];
        $staticSegment = $item->getStaticSegment();
        $channel = $staticSegment->getChannel();
        $this->transport->init($channel->getTransport());

        $items = new ArrayCollection($items);

        $itemsToWrite = [];

        $addedItems = $this->set($items);

        if ($addedItems) {
            $this->logger->info(sprintf('Set Member Extended Merge Vars: [%s] added', count($addedItems)));
        }

        $itemsToWrite = array_merge($itemsToWrite, $addedItems);

        parent::write($itemsToWrite);
    }

    /**
     * @param ArrayCollection $items
     * @return array
     */
    protected function set(ArrayCollection $items)
    {
        $items = $items->filter(function (MemberExtendedMergeVar $mmbrExtdMergeVar) {
            return $mmbrExtdMergeVar->isAddState();
        });

        if ($items->isEmpty()) {
            return [];
        }

        $successItems = [];
        /** @var MemberExtendedMergeVar $mmbrExtendedMergeVar */
        foreach ($items as $mmbrExtendedMergeVar) {
            $requestParams = [
                'id' => $mmbrExtendedMergeVar->getStaticSegment()->getSubscribersList()->getOriginId(),
                'email' => ['email' => $mmbrExtendedMergeVar->getMember()->getEmail()],
                'merge_vars' => $mmbrExtendedMergeVar->getMergeVarValues()
            ];

            try {
                $response = $this->transport->updateListMember($requestParams);

                $this
                    ->handleResponse(
                        $response,
                        function (
                            $response,
                            LoggerInterface $logger
                        ) use (
                            &$successItems,
                            $mmbrExtendedMergeVar,
                            $requestParams
                        ) {
                            if (empty($response['error'])) {
                                $mmbrExtendedMergeVar->markSynced();
                                $successItems[] = $mmbrExtendedMergeVar;
                            }

                            if (!empty($response['errors']) && is_array($response['errors'])) {
                                $logger->error(
                                    'Mailchimp error occurs during execution "updateListMember" method',
                                    [
                                        'requestParams' => $requestParams,
                                    ]
                                );
                            }
                        }
                    );
            } catch (NotSubscribedException $e) {
                $successItems[] = $mmbrExtendedMergeVar;
                $mmbrExtendedMergeVar->setState(MemberExtendedMergeVar::STATE_DROPPED);
                $this->logger->warning(
                    'Mailchimp reports that {email} is not subscribed, marking MemberExtendedMergeVar #{id} as dropped',
                    [
                        'email' => $mmbrExtendedMergeVar->getMember()->getEmail(),
                        'id' => $mmbrExtendedMergeVar->getId(),
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->error(
                    'Exception caught during update member list. Message: "{message}"',
                    ['message' => $e->getMessage(), 'requestParameters' => $requestParams, 'exception' => $e]
                );
            }
        }
        return $successItems;
    }
}
