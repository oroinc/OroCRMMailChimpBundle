<?php

namespace OroCRM\Bundle\MailChimpBundle\EventListener;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Event\ProcessHandleEvent;
use Oro\Bundle\WorkflowBundle\EventListener\ProcessCollectorListener;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;

class ProcessListener
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var ProcessCollectorListener
     */
    protected $processCollectorListener;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ProcessCollectorListener $processCollectorListener
     */
    public function __construct(DoctrineHelper $doctrineHelper, ProcessCollectorListener $processCollectorListener)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->processCollectorListener = $processCollectorListener;
    }

    /**
     * @param ProcessHandleEvent $event
     *
     * @throws \Exception
     */
    public function onProcessHandleAfter(ProcessHandleEvent $event)
    {
        $entity = $event->getProcessData()->get('data');
        if (!$entity instanceof MemberActivity) {
            return;
        }

        $this->processCollectorListener->setEnabled(false);
        try {
            $this->doctrineHelper->getEntityManager($entity)->flush($entity);
        } catch (\Exception $e) {
            $this->processCollectorListener->setEnabled(true);

            throw $e;
        }

        $this->processCollectorListener->setEnabled(true);
    }
}
