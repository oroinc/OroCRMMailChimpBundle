<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Lock;

/**
 * TODO Move this class to OroWorkflowBundle or remove after CRM-1635
 */
class Lock
{
    /**
     * @var bool[]
     */
    protected $locks = [];

    /**
     * @param string $name
     */
    public function lock($name)
    {
        $this->locks[$name] = true;
    }

    /**
     * @param string $name
     */
    public function unlock($name)
    {
        unset($this->locks[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isLocked($name)
    {
        return isset($this->locks[$name]);
    }
}
