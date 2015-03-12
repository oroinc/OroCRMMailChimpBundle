<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Writer\ExtendedMergeVar\AddMergeVars;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Writer\ExtendedMergeVar\Handler;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Writer\ExtendedMergeVar\RemoveMergeVars;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Writer\ExtendedMergeVar\UpdateMergeVars;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

        $itemsToWrite = array();

        $addedItems = $this->add($items);
        $removedItems = $this->remove($items);

        if ($addedItems) {
            $this->logger->info(sprintf('Extended merge vars: [%s] added', count($addedItems)));
        }

        if ($removedItems) {
            $this->logger->info(sprintf('Extended merge vars: [%s] removed', count($addedItems)));
        }

        $itemsToWrite = array_merge($itemsToWrite, $addedItems, $removedItems);

        parent::write($itemsToWrite);
    }

    /**
     * @param ArrayCollection $items
     * @return array
     */
    protected function add(ArrayCollection $items)
    {
        if ($items->isEmpty()) {
            return array();
        }
        $successItems = array();
        /** @var ExtendedMergeVar $each */
        foreach ($items->filter($this->addedItemsFilter()) as $each) {
            $response = $this->transport->addListMergeVar(
                array(
                    'id' => $each->getStaticSegment()->getSubscribersList()->getOriginId(),
                    'tag' => $each->getTag(),
                    'name' => $each->getLabel(),
                    'options' => array(
                        'field_type' => $each->getFieldType(),
                        'require' => $each->getRequire()
                    )
                )
            );
            if (is_array($response)) {
                $this->handleErrorResponse($response);
                if (false === isset($response['errors'])) {
                    $each->setSyncedState();
                    array_push($successItems, $each);
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
            return array();
        }
        $successItems = array();
        /** @var ExtendedMergeVar $each */
        foreach ($items->filter($this->removedItemsFilter()) as $each) {
            $response = $this->transport->deleteListMergeVar(
                array(
                    'id' => $each->getStaticSegment()->getSubscribersList()->getOriginId(),
                    'tag' => $each->getTag()
                )
            );
            if (is_array($response)) {
                $this->handleErrorResponse($response);
                if (false === isset($response['errors'])) {
                    $each->setDroppedState();
                    array_push($successItems, $each);
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
