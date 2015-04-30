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
        $items = $items->filter(function(MemberExtendedMergeVar $mmbrExtdMergeVar) {
            return $mmbrExtdMergeVar->isAddState();
        });

        if ($items->isEmpty()) {
            return [];
        }

        $successItems = [];
        /** @var MemberExtendedMergeVar $mmbrExtendedMergeVar */
        foreach ($items as $mmbrExtendedMergeVar) {
            try {
                $response = $this->transport->updateListMember(
                    [
                        'id' => $mmbrExtendedMergeVar->getStaticSegment()->getSubscribersList()->getOriginId(),
                        'email' => ['email' => $mmbrExtendedMergeVar->getMember()->getEmail()],
                        'merge_vars' => $mmbrExtendedMergeVar->getMergeVarValues()
                    ]
                );

                $this
                    ->handleResponse(
                        $response,
                        function ($response) use (&$successItems, $mmbrExtendedMergeVar) {
                            if (empty($response['error'])) {
                                $mmbrExtendedMergeVar->markSynced();
                                $successItems[] = $mmbrExtendedMergeVar;
                            }
                        }
                    );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->stepExecution->addFailureException($e);
            }
        }
        return $successItems;
    }
}
