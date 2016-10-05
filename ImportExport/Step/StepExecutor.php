<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Step;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;

use Oro\Bundle\BatchBundle\Step\StepExecutionWarningHandlerInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutor as BaseStepExecutor;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use Oro\Bundle\MailChimpBundle\ImportExport\Reader\SubordinateReaderInterface;

class StepExecutor extends BaseStepExecutor
{
    /**
     * @var ItemReaderInterface|IteratorBasedReader
     */
    protected $reader;

    /**
     * {@inheritdoc}
     */
    public function execute(StepExecutionWarningHandlerInterface $warningHandler = null)
    {
        $itemsToWrite = [];
        $writeCount = 0;
        $scheduleWrite = false;

        try {
            $stopExecution = false;
            while (!$stopExecution) {
                try {
                    $readItem = $this->reader->read();
                    if (null === $readItem) {
                        $stopExecution = true;
                        continue;
                    }
                } catch (InvalidItemException $e) {
                    $this->handleStepExecutionWarning($this->reader, $e, $warningHandler);

                    continue;
                }

                $processedItem = $this->process($readItem, $warningHandler);
                if (null !== $processedItem) {
                    $itemsToWrite[] = $processedItem;
                    $writeCount++;
                    if (0 === $writeCount % $this->batchSize || $scheduleWrite) {
                        $this->write($itemsToWrite, $warningHandler);
                        $itemsToWrite = [];
                        $scheduleWrite = false;
                    }
                }

                if ($this->reader instanceof IteratorBasedReader) {
                    $sourceIterator = $this->reader->getSourceIterator();
                    if ($sourceIterator instanceof SubordinateReaderInterface && $sourceIterator->writeRequired()) {
                        $scheduleWrite = true;
                    }
                }
            }

            if (count($itemsToWrite) > 0) {
                $this->write($itemsToWrite, $warningHandler);
            }

            $this->ensureResourcesReleased($warningHandler);
        } catch (\Exception $error) {
            $this->ensureResourcesReleased($warningHandler);
            throw $error;
        }
    }
}
