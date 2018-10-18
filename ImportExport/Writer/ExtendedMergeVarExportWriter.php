<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Oro\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;
use Psr\Log\LoggerInterface;

class ExtendedMergeVarExportWriter extends AbstractExportWriter
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

        try {
            $addedItems = $this->add($items);
            $removedItems = $this->remove($items);

            if ($addedItems) {
                $this->logger->info(sprintf('Extended merge vars: [%s] added', count($addedItems)));
            }

            if ($removedItems) {
                $this->logger->info(sprintf('Extended merge vars: [%s] removed', count($addedItems)));
            }

            $itemsToWrite = array_merge($itemsToWrite, $addedItems, $removedItems);
        } catch (Exception $e) {
            $this->logger->error('Extended merge vars error occurs', ['exception' => $e]);
        }

        parent::write($itemsToWrite);
    }

    /**
     * @param ArrayCollection $items
     * @return array
     * @throws Exception
     */
    protected function add(ArrayCollection $items)
    {
        $items = $items->filter(function (ExtendedMergeVar $extendedMergeVar) {
            return $extendedMergeVar->isAddState();
        });

        if ($items->isEmpty()) {
            return [];
        }

        $mergeVars = $this->getSubscribersListMergeVars(
            $items->first()->getStaticSegment()->getSubscribersList()
        );

        $successItems = [];
        /** @var ExtendedMergeVar $each */
        foreach ($items as $each) {
            $exists = array_filter($mergeVars, function ($var) use ($each) {
                return $var['tag'] === $each->getTag();
            });

            $response = [];
            if (empty($exists)) {
                $response = $this->transport->addListMergeVar([
                    'list_id' => $each->getStaticSegment()->getSubscribersList()->getOriginId(),
                    'tag' => $each->getTag(),
                    'name' => $each->getLabel(),
                    'type' => $each->getFieldType(),
                    'required' => $each->isRequired()
                ]);
            }

            $this->handleResponse(
                $response,
                function ($response, LoggerInterface $logger) use (&$successItems, $each) {
                    if (empty($response['errors'])) {
                        $each->markSynced();
                        $successItems[] = $each;
                    }

                    if (!empty($response['errors']) && is_array($response['errors'])) {
                        $logger->error(
                            'Mailchimp error occurs during execution "addListMergeVar" method',
                            [
                                'each_id' => $each->getId(),
                                'each_label' => $each->getLabel(),
                            ]
                        );
                    }
                }
            );
        }

        return $successItems;
    }

    /**
     * @param ArrayCollection $items
     * @return array
     */
    protected function remove(ArrayCollection $items)
    {
        $items = $items->filter(function (ExtendedMergeVar $extendedMergeVar) {
            return $extendedMergeVar->isRemoveState();
        });

        if ($items->isEmpty()) {
            return [];
        }
        $successItems = [];
        /** @var ExtendedMergeVar $each */
        foreach ($items as $each) {
            $each->markDropped();
            $successItems[] = $each;
        }
        return $successItems;
    }
}
