<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;

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
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }

        parent::write($itemsToWrite);
    }

    /**
     * @param ArrayCollection $items
     * @return array
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
                $response = $this->transport->addListMergeVar(
                    [
                        'id' => $each->getStaticSegment()->getSubscribersList()->getOriginId(),
                        'tag' => $each->getTag(),
                        'name' => $each->getLabel(),
                        'options' => [
                            'field_type' => $each->getFieldType(),
                            'require' => $each->isRequired()
                        ]
                    ]
                );
            }

            $this
                ->handleResponse(
                    $response,
                    function ($response) use (&$successItems, $each) {
                        if (empty($response['errors'])) {
                            $each->markSynced();
                            $successItems[] = $each;
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

    /**
     * @param SubscribersList $subscribersList
     * @return array
     *
     * @deprecated in 2.5 this method will be removed from here to parent class
     */
    protected function getSubscribersListMergeVars(SubscribersList $subscribersList)
    {
        return parent::getSubscribersListMergeVars($subscribersList);
    }

    /**
     * @param array $response
     * @return array
     *
     * @deprecated in 2.5 this method will be removed from here to parent class
     */
    protected function extractMergeVarsFromResponse(array $response)
    {
        return parent::extractMergeVarsFromResponse($response);
    }
}
