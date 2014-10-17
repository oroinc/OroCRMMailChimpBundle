<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Action;

/**
 * TODO Move this class to OroWorkflowBundle or remove after CRM-1635
 */
class Unlock extends AbstractLockAction
{
    const NAME = 'unlock';

    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        $this->lock->unlock($this->getLockName($context));
    }

    /**
     * @return string
     */
    protected function getActionName()
    {
        return self::NAME;
    }
}
