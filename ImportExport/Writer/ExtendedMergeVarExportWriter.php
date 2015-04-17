<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

class ExtendedMergeVarExportWriter extends AbstractExportWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var ExtendedMergeVar $item */
        $item = reset($items);
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
        $items = $items->filter($this->addedItemsFilter());

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
                            'require' => $each->getRequire()
                        ]
                    ]
                );
            }
            if (is_array($response)) {
                $this->handleErrorResponse($response);
                if (!isset($response['errors']) || empty($response['errors'])) {
                    $each->markSynced();
                    $successItems[] = $each;
                }
            }
        }
        return $successItems;
    }

    /**
     * @param ArrayCollection $items
     * @return array
     */
    protected function remove(ArrayCollection $items)
    {
        if ($items->isEmpty()) {
            return [];
        }
        $successItems = [];
        /** @var ExtendedMergeVar $each */
        foreach ($items->filter($this->removedItemsFilter()) as $each) {
            $each->markDropped();
            array_push($successItems, $each);
        }
        return $successItems;
    }

    /**
     * @param SubscribersList $subscribersList
     * @return array
     */
    protected function getSubscribersListMergeVars(SubscribersList $subscribersList)
    {
        $response = $this->transport->getListMergeVars(
            [
                'id' => [
                    $subscribersList->getOriginId()
                ]
            ]
        );

        if (false === is_array($response)) {
            throw new \RuntimeException('Can not get list of merge vars.');
        }

        $this->handleErrorResponse($response);

        if (isset($response['errors']) && !empty($response['errors'])) {
            throw new \RuntimeException('Can not get list of merge vars.');
        }

        return $this->extractMergeVarsFromResponse($response);
    }

    /**
     * @param array $response
     * @return array
     */
    protected function extractMergeVarsFromResponse(array $response)
    {
        if (!isset($response['data'])) {
            throw new \RuntimeException('Can not extract merge vars data from response.');
        }
        $data = reset($response['data']);
        if (!isset($data['merge_vars'])) {
            return [];
        }
        return $data['merge_vars'];
    }

    /**
     * @return callable
     */
    protected function addedItemsFilter()
    {
        return function (ExtendedMergeVar $extendedMergeVar) {
            return $extendedMergeVar->isAddState();
        };
    }

    /**
     * @return callable
     */
    protected function removedItemsFilter()
    {
        return function (ExtendedMergeVar $extendedMergeVar) {
            return $extendedMergeVar->isRemoveState();
        };
    }

    /**
     * @param array $response
     * @return void
     */
    protected function handleErrorResponse(array $response)
    {
        if (isset($response['errors']) && !empty($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $this->logErrors(array('code' => $error['code'], 'error' => $error['error']));
            }
            throw new \RuntimeException('Can not get list of merge vars.');
        }
    }

    /**
     * @param array $errors
     * @return void
     */
    private function logErrors(array $errors)
    {
        if (empty($errors)) {
            return;
        }
        foreach ($errors as $error) {
            $this->logger->warning(
                sprintf('[Error #%s] %s', $error['code'], $error['error'])
            );
        }
    }
}
