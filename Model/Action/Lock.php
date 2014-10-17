<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Action;

/**
 * TODO Move this class to OroWorkflowBundle or remove after CRM-1635
 */
class Lock extends AbstractLockAction
{
    const NAME = 'lock';

    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        $this->lock->lock($this->getLockName($context));
    }

    /**
     * @return string
     */
    protected function getActionName()
    {
        return self::NAME;
    }
}
