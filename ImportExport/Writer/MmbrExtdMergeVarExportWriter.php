<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar;

class MmbrExtdMergeVarExportWriter extends AbstractExportWriter
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
        $items = $items->filter($this->addedItemsFilter());

        if ($items->isEmpty()) {
            return [];
        }

        $successItems = [];
        /** @var MemberExtendedMergeVar $mmbrExtdMergeVar */
        foreach ($items as $mmbrExtdMergeVar) {
            $response = $this->transport->updateListMember(
                [
                    'id' => $mmbrExtdMergeVar->getStaticSegment()->getSubscribersList()->getOriginId(),
                    'email' => ['email' => $mmbrExtdMergeVar->getMember()->getEmail()],
                    'merge_vars' => $mmbrExtdMergeVar->getMergeVarValues()
                ]
            );

            if (is_array($response)) {
                $this->handleErrorResponse($response);
                if (!isset($response['errors']) || empty($response['errors'])) {
                    $mmbrExtdMergeVar->setSyncedState();
                    array_push($successItems, $mmbrExtdMergeVar);
                }
            }
        }
        return $successItems;
    }

    /**
     * @return callable
     */
    protected function addedItemsFilter()
    {
        return function (MemberExtendedMergeVar $mmbrExtdMergeVar) {
            return $mmbrExtdMergeVar->isAddState();
        };
    }

    /**
     * @param array $response
     * @return void
     */
    protected function handleErrorResponse(array $response)
    {
        if (isset($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $this->logErrors(array('code' => $error['code'], 'error' => $error['error']));
            }
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
